<?php

namespace Floorplate\Anchors\Fieldtypes;

use Floorplate\Anchors\Rules\UniqueAnchor;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Str;

/**
 * Declares an in-page anchor on the block (or entry) it lives on. The stored
 * value is what templates render as the section's HTML id. Anchor Link fields
 * reference the declaring block by its set id, so renaming an anchor here
 * never breaks links pointing at it.
 */
class Anchor extends Fieldtype
{
    protected static $title = 'Anchor';

    // Renders with the core text input; this fieldtype is PHP-only.
    protected $component = 'text';

    protected $categories = ['special'];

    protected $icon = 'text';

    public function process($data)
    {
        return filled($data) ? Str::slug(trim($data)) : null;
    }

    public function rules(): array
    {
        return [new UniqueAnchor($this->field)];
    }
}
