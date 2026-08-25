<?php

namespace Floorplate\Anchors\Rules;

use Closure;
use Floorplate\Anchors\Support\AnchorFinder;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Fields\Field;
use Statamic\Support\Str;

/**
 * Rejects an anchor that another field in the same publish payload also
 * declares. Attached per anchor field, so every offending field gets the
 * inline error. Comparison is slug-to-slug: "Contact Us" collides with
 * "contact-us".
 */
class UniqueAnchor implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function __construct(protected Field $field) {}

    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value) || ! $blueprint = $this->blueprint()) {
            return;
        }

        $slug = Str::slug(trim($value));

        $duplicates = app(AnchorFinder::class)
            ->anchorsInPayload($blueprint, $this->data)
            ->filter(fn ($anchor, $path) => $anchor === $slug && $path !== $attribute);

        if ($duplicates->isNotEmpty()) {
            $fail("The anchor \"{$slug}\" is used more than once on this page.");
        }
    }

    protected function blueprint()
    {
        $parent = $this->field->parent();

        return match (true) {
            $parent instanceof EntryContract => $parent->blueprint(),
            // Creating an entry: the parent is the collection itself.
            $parent instanceof CollectionContract => $parent->entryBlueprint(),
            is_object($parent) && method_exists($parent, 'blueprint') => $parent->blueprint(),
            default => null, // No blueprint context — skip uniqueness rather than block saving.
        };
    }
}
