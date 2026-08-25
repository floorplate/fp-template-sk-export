<?php

namespace Floorplate\Anchors\Support;

use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Replicator;
use Statamic\Support\Arr;
use Statamic\Support\Str;

/**
 * Finds anchor declarations by walking a blueprint's fields for the `anchor`
 * fieldtype — never by handle — so it works with any blueprint structure.
 * Traverses top-level fields plus Replicator/Bard sets (Bard extends
 * Replicator); grids or groups nested inside sets are not traversed.
 */
class AnchorFinder
{
    /**
     * Anchors in a publish payload, keyed by the validator's dotted attribute
     * path (e.g. content_blocks.3.anchor_id). Includes disabled rows — a
     * disabled duplicate would still collide when re-enabled.
     *
     * @return Collection<string, string> path => slug
     */
    public function anchorsInPayload(Blueprint $blueprint, array $payload): Collection
    {
        return $this->records($blueprint, $payload)
            ->mapWithKeys(fn ($record) => [$record['path'] => $record['anchor']]);
    }

    /**
     * Anchors declared on a saved entry, for pickers and augmentation.
     * Disabled rows are excluded — they don't render.
     *
     * @return Collection<int, array{set: ?string, anchor: string, label: string}>
     */
    public function anchorsInEntry(Entry $entry): Collection
    {
        return $this->records($entry->blueprint(), $entry->data()->all())
            ->filter(fn ($record) => $record['enabled'])
            ->map(fn ($record) => Arr::only($record, ['set', 'anchor', 'label', 'display']))
            ->values();
    }

    /**
     * The current anchor declared by the set with the given id — the
     * rename-proof lookup. Searches disabled rows too: the current (hidden)
     * value beats a stale snapshot.
     */
    public function currentAnchorForSet(Entry $entry, string $setId): ?string
    {
        return $this->records($entry->blueprint(), $entry->data()->all())
            ->firstWhere('set', $setId)['anchor'] ?? null;
    }

    /**
     * Describes where anchor fields live in a blueprint, so a client that holds
     * an entry's values but not its blueprint can find the anchors itself. Nav
     * item fields get no entry context from Statamic, so the CP resolves
     * anchors from the target entry's values carried in the publish container.
     *
     * @return array{fields: list<string>, replicators: array<string, array<string, array{display: string, fields: list<string>}>>}
     */
    public function fieldMap(Blueprint $blueprint): array
    {
        $topLevel = [];
        $replicators = [];

        foreach ($blueprint->fields()->all() as $handle => $field) {
            if ($this->isAnchor($field)) {
                $topLevel[] = $handle;

                continue;
            }

            $fieldtype = $field->fieldtype();

            if (! $fieldtype instanceof Replicator) {
                continue;
            }

            $sets = [];

            foreach ($fieldtype->flattenedSetsConfig()->all() as $setHandle => $setConfig) {
                $anchorFields = collect($fieldtype->fields($setHandle)->all())
                    ->filter(fn ($setField) => $this->isAnchor($setField))
                    ->keys()
                    ->all();

                if (empty($anchorFields)) {
                    continue;
                }

                $sets[$setHandle] = [
                    'display' => $setConfig['display'] ?? Str::title(str_replace('_', ' ', $setHandle)),
                    'fields' => $anchorFields,
                ];
            }

            if (! empty($sets)) {
                $replicators[$handle] = $sets;
            }
        }

        return ['fields' => $topLevel, 'replicators' => $replicators];
    }

    /**
     * @return Collection<int, array{path: string, set: ?string, anchor: string, label: string, enabled: bool}>
     */
    private function records(Blueprint $blueprint, array $values): Collection
    {
        $records = collect();

        foreach ($blueprint->fields()->all() as $handle => $field) {
            $value = $values[$handle] ?? null;

            if ($this->isAnchor($field)) {
                if (filled($value)) {
                    $slug = Str::slug(trim($value));
                    $records->push([
                        'path' => $handle,
                        'set' => null,
                        'anchor' => $slug,
                        'display' => $field->display() ?? Str::title(str_replace('-', ' ', $slug)),
                        'label' => $slug,
                        'enabled' => true,
                    ]);
                }

                continue;
            }

            $fieldtype = $field->fieldtype();

            if (! $fieldtype instanceof Replicator || ! is_array($value)) {
                continue;
            }

            $setsConfig = $fieldtype->flattenedSetsConfig()->all();

            // Original indexes must be preserved: validator paths are built
            // from row positions, and Bard's non-set nodes occupy indexes.
            foreach ($value as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                // Bard stores set rows as ProseMirror nodes with the set's
                // values nested under attrs; Replicator rows are flat.
                $isBardSet = ($row['type'] ?? null) === 'set' && isset($row['attrs']['values']);
                $rowValues = $isBardSet ? $row['attrs']['values'] : $row;
                $setHandle = $rowValues['type'] ?? null;

                if (! $setHandle || ! isset($setsConfig[$setHandle])) {
                    continue;
                }

                $records = $records->merge($this->setRecords(
                    $fieldtype, $setsConfig, $handle, $index, $isBardSet, $row, $rowValues
                ));
            }
        }

        return $records;
    }

    private function setRecords(
        Replicator $fieldtype,
        array $setsConfig,
        string $handle,
        int $index,
        bool $isBardSet,
        array $row,
        array $rowValues
    ): Collection {
        $setHandle = $rowValues['type'];

        // Publish payloads key rows by _id; saved content uses id.
        $setId = $isBardSet
            ? Arr::get($row, 'attrs.id')
            : ($row['id'] ?? $row['_id'] ?? null);

        $enabled = ($isBardSet
            ? Arr::get($row, 'attrs.enabled', true)
            : ($row['enabled'] ?? true)) !== false;

        $pathPrefix = $isBardSet
            ? "{$handle}.{$index}.attrs.values"
            : "{$handle}.{$index}";

        $display = Arr::get($setsConfig, "{$setHandle}.display")
            ?? Str::title(str_replace('_', ' ', $setHandle));

        $records = collect();

        foreach ($fieldtype->fields($setHandle)->all() as $setFieldHandle => $setField) {
            if (! $this->isAnchor($setField) || blank($rowValues[$setFieldHandle] ?? null)) {
                continue;
            }

            $slug = Str::slug(trim($rowValues[$setFieldHandle]));

            $records->push([
                'path' => "{$pathPrefix}.{$setFieldHandle}",
                'set' => $setId,
                'anchor' => $slug,
                'display' => $display,
                // Label drives combobox search — cover both the section name and the anchor.
                'label' => "{$display} #{$slug}",
                'enabled' => $enabled,
            ]);
        }

        return $records;
    }

    private function isAnchor(Field $field): bool
    {
        return $field->type() === 'anchor';
    }
}
