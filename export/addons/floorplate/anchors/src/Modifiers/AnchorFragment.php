<?php

namespace Floorplate\Anchors\Modifiers;

use Floorplate\Anchors\Support\AnchorFinder;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Fields\Value;
use Statamic\Modifiers\Modifier;

/**
 * Turns a Section Anchor value into a URL fragment, resolved against the entry
 * the surrounding item links to.
 *
 *     <a href="{{ url }}{{ section_anchor | anchor_fragment }}">
 *
 * The target entry comes from the context (a nav item exposes `entry_id`),
 * because a nav item's fields get no entry context of their own. Resolution
 * prefers the declaring block's CURRENT anchor so renaming it never breaks the
 * link, then falls back to the stored snapshot if that block is gone. Returns
 * an empty string when there is nothing to append, so it is always safe to
 * concatenate onto a URL.
 */
class AnchorFragment extends Modifier
{
    public function index($value, $params, $context)
    {
        $value = $this->unwrap($value);

        if (! is_array($value)) {
            return '';
        }

        $anchor = $this->resolve($value, $this->entry($params, $context));

        return $anchor ? '#'.$anchor : '';
    }

    private function resolve(array $value, ?EntryContract $entry): ?string
    {
        $setId = $value['set'] ?? null;

        if ($entry && $setId) {
            $current = Blink::once("anchors-set-{$entry->id()}-{$setId}", function () use ($entry, $setId) {
                return app(AnchorFinder::class)->currentAnchorForSet($entry, $setId);
            });

            if ($current) {
                return $current;
            }
        }

        return $value['anchor'] ?? null;
    }

    /**
     * The entry to resolve against: an explicit `:param`, else the context's
     * `entry_id` (nav items) or `id` (an entry rendering its own data).
     */
    private function entry($params, $context): ?EntryContract
    {
        $id = $this->unwrap($params[0] ?? null)
            ?: $this->unwrap($context['entry_id'] ?? null)
            ?: $this->unwrap($context['id'] ?? null);

        return is_string($id) ? Facades\Entry::find($id) : null;
    }

    private function unwrap($value)
    {
        return $value instanceof Value ? $value->value() : $value;
    }
}
