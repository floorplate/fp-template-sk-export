<?php

namespace Floorplate\StillStreamAgent;

use Floorplate\FleetAgent\Support\FleetClient;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    /** The same permission handles Floorplate checks on its side. */
    public const PERMISSIONS = [
        'create stillstream projects' => 'Create & Edit Projects',
        'generate stillstream media' => 'Generate Clips & Music',
        'render stillstream videos' => 'Render Videos',
        'purchase stillstream credits' => 'Purchase Credits',
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'input' => [
            'resources/js/agent.js',
            'resources/css/addon.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function bootAddon()
    {
        Permission::extend(function () {
            Permission::group('stillstream', 'StillStream', function () {
                Permission::register('access stillstream')->label('Access StillStream')->children(
                    collect(self::PERMISSIONS)->map(fn (string $label, string $handle) => Permission::make($handle)->label($label))->values()->all()
                );
            });
        });

        Nav::extend(function ($nav) {
            // A site that is not connected to Floorplate has nothing to show.
            if (! FleetClient::configured()) {
                return;
            }

            $nav->create('Projects')->section('StillStream')->route('stillstream-agent.projects.index')->icon('movie-video-clip')->can('access stillstream');
            $nav->create('Credits')->section('StillStream')->route('stillstream-agent.wallet')->icon('credit-card')->can('access stillstream');
        });
    }
}
