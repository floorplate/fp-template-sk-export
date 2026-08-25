<?php

use Statamic\Console\Processes\Composer;

/**
 * Wires the bundled Anchors addon into the freshly installed site.
 *
 * The addon ships as files under addons/floorplate/anchors rather than as a
 * Packagist package, so it cannot be listed in starter-kit.yaml's
 * `dependencies` — the installer dry-runs those against Packagist and would
 * abort. Instead we register a local path repository and require it here.
 *
 * Safe to re-run: the repository entry is only added when missing, and
 * `composer require` on an already-installed package is a no-op.
 */
class StarterKitPostInstall
{
    private const PACKAGE = 'floorplate/anchors';

    private const PATH = 'addons/floorplate/anchors';

    public function handle($console): void
    {
        if (! is_dir(base_path(self::PATH))) {
            $console->warn('Anchors addon not found at ['.self::PATH.']; skipping. In-page anchor links will be unavailable.');

            return;
        }

        $this->registerPathRepository($console);

        $console->line('Installing addon ['.self::PACKAGE.']');

        Composer::withoutQueue()->throwOnFailure()->require(self::PACKAGE, '*');

        $this->pruneStaleAssets($console);
    }

    /**
     * Deletes control panel bundles left behind by previous kit versions.
     *
     * Installing a starter kit copies files without syncing — it enumerates the
     * incoming kit and never deletes — so each release whose bundle filename
     * changes leaves the old, content-hashed one behind in both the addon and
     * the published copy. Harmless, since the manifest names the current file,
     * but it accretes on every deploy.
     */
    private function pruneStaleAssets($console): void
    {
        $roots = [
            base_path(self::PATH.'/resources/dist/build'),
            public_path('vendor/anchors/build'),
        ];

        $removed = 0;

        foreach ($roots as $root) {
            $manifest = $root.'/manifest.json';

            if (! is_file($manifest)) {
                continue;
            }

            $current = collect(json_decode(file_get_contents($manifest), true) ?: [])
                ->pluck('file')
                ->filter()
                ->map(fn ($file) => $root.'/'.$file)
                ->all();

            if (empty($current)) {
                continue;
            }

            foreach (glob($root.'/assets/*') ?: [] as $asset) {
                if (is_file($asset) && ! in_array($asset, $current, true)) {
                    unlink($asset);
                    $removed++;
                }
            }
        }

        if ($removed > 0) {
            $console->line("Removed {$removed} stale asset(s) from previous versions.");
        }
    }

    private function registerPathRepository($console): void
    {
        $file = base_path('composer.json');
        $json = json_decode(file_get_contents($file), true);

        if (! is_array($json)) {
            $console->error('Could not read composer.json; skipping Anchors addon setup.');

            return;
        }

        $repositories = $json['repositories'] ?? [];

        foreach ($repositories as $repository) {
            if (($repository['url'] ?? null) === self::PATH) {
                return;
            }
        }

        $repositories[] = [
            'type' => 'path',
            'url' => self::PATH,
            'options' => ['symlink' => true],
        ];

        $json['repositories'] = $repositories;

        file_put_contents(
            $file,
            json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
        );
    }
}
