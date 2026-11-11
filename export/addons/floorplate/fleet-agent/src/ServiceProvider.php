<?php

namespace Floorplate\FleetAgent;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        Console\SyncAdmin::class,
    ];

    protected $routes = [
        'web' => __DIR__.'/../routes/web.php',
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $widgets = [
        Widgets\FathomAnalytics::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/fleet-agent.php', 'fleet-agent');
    }
}
