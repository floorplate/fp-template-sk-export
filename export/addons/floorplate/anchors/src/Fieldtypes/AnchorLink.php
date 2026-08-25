<?php

namespace Floorplate\Anchors\Fieldtypes;

use Floorplate\Anchors\Support\AnchorFinder;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

/**
 * A link picker with URL, entry, and in-page-anchor modes.
 *
 * Stored value shapes:
 *  - plain string                                        legacy content / URL mode
 *  - {mode: entry, entry: id, set?: id, anchor?: slug}   entry, optionally an anchor on it
 *  - {mode: anchor, set: id, anchor: slug}               anchor on the current entry
 *
 * Anchor references store both the declaring block's set id and a slug
 * snapshot. Augmentation resolves the set's CURRENT anchor first (so renames
 * never break links), falls back to the snapshot (deleted block), then to the
 * bare entry URL. Always augments to a plain URL string so templates can use
 * href="{{ link }}" for every shape.
 */
class AnchorLink extends Fieldtype
{
    protected static $title = 'Anchor Link';

    protected $categories = ['relationship'];

    protected $icon = 'link';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => 'Input Behavior',
                'fields' => [
                    'collections' => [
                        'display' => 'Collections',
                        'instructions' => 'Collections available in entry mode. Defaults to all routable collections.',
                        'type' => 'collections',
                        'mode' => 'select',
                        'width' => 50,
                    ],
                    'modes' => [
                        'display' => 'Modes',
                        'instructions' => 'Link modes editors can choose. Leave empty for all.',
                        'type' => 'checkboxes',
                        'inline' => true,
                        'width' => 50,
                        'options' => [
                            'url' => 'URL',
                            'entry' => 'Entry',
                            'anchor' => 'This page',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function preProcess($data)
    {
        return $data;
    }

    public function process($data)
    {
        if (blank($data)) {
            return null;
        }

        if (is_string($data)) {
            return $data;
        }

        // URL mode collapses to a plain string so content files stay in the
        // same shape legacy values already use.
        if (($data['mode'] ?? 'url') === 'url') {
            return filled($data['url'] ?? null) ? $data['url'] : null;
        }

        $data = Arr::only(array_filter($data, fn ($value) => filled($value)), [
            'mode', 'entry', 'set', 'anchor',
        ]);

        return count($data) > 1 ? $data : null;
    }

    public function augment($value)
    {
        if (blank($value)) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        return match ($value['mode'] ?? 'url') {
            'url' => $value['url'] ?? null,
            'anchor' => $this->sameEntryUrl($value),
            'entry' => $this->entryUrl($value),
            default => null,
        };
    }

    public function preProcessIndex($data)
    {
        return $this->augment($data);
    }

    private function sameEntryUrl(array $value): ?string
    {
        $parent = $this->field->parent();

        $anchor = $parent instanceof EntryContract
            ? $this->resolveAnchor($parent, $value)
            : ($value['anchor'] ?? null);

        return $anchor ? '#'.$anchor : null;
    }

    private function entryUrl(array $value): ?string
    {
        if (! $entry = Facades\Entry::find($value['entry'] ?? null)) {
            return null;
        }

        $anchor = $this->resolveAnchor($entry, $value);

        return $anchor ? $entry->url().'#'.$anchor : $entry->url();
    }

    private function resolveAnchor(EntryContract $target, array $value): ?string
    {
        if ($setId = $value['set'] ?? null) {
            $current = Blink::once("anchors-set-{$target->id()}-{$setId}", function () use ($target, $setId) {
                return app(AnchorFinder::class)->currentAnchorForSet($target, $setId);
            });

            if ($current) {
                return $current;
            }
        }

        return $value['anchor'] ?? null;
    }

    public function preload()
    {
        $value = $this->field->value();
        $entryId = is_array($value) ? ($value['entry'] ?? null) : null;
        $targetEntry = $entryId ? Facades\Entry::find($entryId) : null;

        $entriesFieldtype = $this->nestedEntriesFieldtype($entryId);

        return [
            'initialMode' => is_array($value) ? ($value['mode'] ?? 'url') : (filled($value) ? 'url' : null),
            'initialUrl' => is_string($value) ? $value : null,
            'initialSelectedEntries' => $entryId ? [$entryId] : [],
            'initialSet' => is_array($value) ? ($value['set'] ?? null) : null,
            'initialAnchor' => is_array($value) ? ($value['anchor'] ?? null) : null,
            'initialTargetAnchors' => $targetEntry
                ? app(AnchorFinder::class)->anchorsInEntry($targetEntry)->all()
                : [],
            // "This page" needs an entry to scan; hide it on globals, nav, etc.
            'showAnchorMode' => $this->field->parent() instanceof EntryContract,
            'anchorsIndexUrl' => cp_route('anchors.entries.show', ['entryId' => '_ID_']),
            'entry' => [
                'config' => $entriesFieldtype->config(),
                'meta' => $entriesFieldtype->preload(),
            ],
        ];
    }

    private function nestedEntriesFieldtype($value): Fieldtype
    {
        $entryField = new Field('entry', [
            'type' => 'entries',
            'max_items' => 1,
            'create' => false,
        ]);

        $entryField->setValue($value);

        $entryField->setConfig(array_merge(
            $entryField->config(),
            ['collections' => $this->collections()]
        ));

        return $entryField->fieldtype();
    }

    private function collections()
    {
        $collections = $this->config('collections');

        if (empty($collections)) {
            $site = Site::current()->handle();

            $collections = Blink::once('anchors-routable-collections-'.$site, function () use ($site) {
                return Facades\Collection::all()
                    ->reject(fn ($collection) => is_null($collection->route($site)))
                    ->map->handle()
                    ->values()
                    ->all();
            });
        }

        return $collections;
    }
}
