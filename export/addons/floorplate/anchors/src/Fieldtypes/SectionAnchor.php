<?php

namespace Floorplate\Anchors\Fieldtypes;

use Floorplate\Anchors\Support\AnchorFinder;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

/**
 * An optional anchor on the entry the surrounding item already links to.
 *
 * Built for navigation items, which receive no entry context from Statamic —
 * `$field->parent()` is null both when the CP builds the publish form and when
 * the nav renders. So this stores only the declaring block's set id plus a slug
 * snapshot, and the `anchor_fragment` modifier performs the rename-proof
 * resolution at render time using the nav item's own `entry_id`.
 *
 * Stored value: {set: <id>, anchor: <slug>}, or null for "entire page".
 */
class SectionAnchor extends Fieldtype
{
    protected static $title = 'Section Anchor';

    protected $categories = ['relationship'];

    protected $icon = 'link';

    public function process($data)
    {
        if (blank($data) || blank($data['set'] ?? null)) {
            return null;
        }

        return Arr::only($data, ['set', 'anchor']);
    }

    /**
     * Left in its stored shape rather than resolved to a URL: resolution needs
     * the target entry, which only the surrounding template knows about.
     */
    public function augment($value)
    {
        return blank($value) ? null : $value;
    }

    public function preProcessIndex($data)
    {
        return blank($data) ? null : '#'.($data['anchor'] ?? '?');
    }

    public function preload()
    {
        return [
            'fieldMap' => $this->fieldMap(),
        ];
    }

    /**
     * Where anchor fields live, merged across every entry blueprint.
     *
     * A union rather than a per-blueprint lookup because the publish container
     * carries the target entry's values but neither its blueprint nor its id —
     * the `blueprint` key is dropped, since the nav blueprint does not declare
     * it. Rows are matched on their own set handle, so merging is safe.
     *
     * @return array{fields: list<string>, replicators: array<string, array>}
     */
    private function fieldMap(): array
    {
        return Blink::once('anchors-field-map', function () {
            $finder = app(AnchorFinder::class);
            $merged = ['fields' => [], 'replicators' => []];

            $blueprints = Facades\Collection::all()
                ->flatMap(fn ($collection) => $collection->entryBlueprints());

            foreach ($blueprints as $blueprint) {
                $map = $finder->fieldMap($blueprint);

                $merged['fields'] = array_values(array_unique(
                    array_merge($merged['fields'], $map['fields'])
                ));

                foreach ($map['replicators'] as $handle => $sets) {
                    $merged['replicators'][$handle] = array_merge(
                        $merged['replicators'][$handle] ?? [],
                        $sets
                    );
                }
            }

            return $merged;
        });
    }
}
