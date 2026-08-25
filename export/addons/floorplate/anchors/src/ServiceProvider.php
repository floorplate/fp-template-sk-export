<?php

namespace Floorplate\Anchors;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        Fieldtypes\Anchor::class,
        Fieldtypes\AnchorLink::class,
        Fieldtypes\SectionAnchor::class,
    ];

    protected $modifiers = [
        Modifiers\AnchorFragment::class,
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];
}
