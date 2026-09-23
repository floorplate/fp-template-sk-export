<?php

namespace Floorplate\FloorplateStarterKit;

use Floorplate\FloorplateStarterKit\StaticCaching\ApplicationCacher;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use Statamic\StaticCaching\StaticCacheManager;

class ServiceProvider extends AddonServiceProvider
{
    /**
     * Statamic 6.33 takes the shared-error copy after the CSRF and nocache replacers
     * run. 6.19 caches the raw response, which would hand the first visitor's CSRF
     * token to everyone who hits a 404, so sharing stays off below this version.
     */
    private const SAFE_SHARED_ERRORS_VERSION = '6.33.0';

    /**
     * Swap in the kit's half-measure cacher. Hooked on the manager's resolution so
     * the override is in place before any static cache driver is built.
     */
    public function register()
    {
        parent::register();

        $this->app->extend(StaticCacheManager::class, function (StaticCacheManager $manager) {
            return $manager->extend('application', function ($app, array $config) {
                return new ApplicationCacher($this->cacheStore(), $config);
            });
        });
    }

    /**
     * With per-URL error entries gone, serve every 404 from one shared copy rather
     * than rendering it for each scanner probe. On older Statamic the styled 404
     * still renders on each request; it just isn't cached.
     */
    public function bootAddon()
    {
        if (version_compare(Statamic::version(), self::SAFE_SHARED_ERRORS_VERSION, '>=')) {
            config(['statamic.static_caching.share_errors' => true]);
        }
    }
}
