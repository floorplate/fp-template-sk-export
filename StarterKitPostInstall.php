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
        $this->seedForms($console);

        if (! is_dir(base_path(self::PATH))) {
            $console->warn('Anchors addon not found at ['.self::PATH.']; skipping. In-page anchor links will be unavailable.');

            return;
        }

        $this->registerPathRepository($console);

        $console->line('Installing addon ['.self::PACKAGE.']');

        // Resolved from the container rather than via Statamic's real-time
        // `Facades\` alias, which is not reliably autoloadable from a hook file
        // that the installer pulls in with require_once.
        app(Composer::class)->withoutQueue()->throwOnFailure()->require(self::PACKAGE, '*');

        $this->pruneStaleAssets($console);
    }

    /**
     * Installs the kit's forms into a site that does not have them yet.
     *
     * Forms are the one part of the kit a site owns outright: the CP writes the
     * notification recipients into resources/forms/<handle>.yaml and any field
     * an editor adds into resources/blueprints/forms/<handle>.yaml. Neither
     * path is in starter-kit.yaml's export_paths, because the installer copies
     * what it ships unconditionally on EVERY deploy and would reset both.
     *
     * So the kit carries resources/form-seeds instead, and we copy from it —
     * strictly when the destination is absent. A site that already has the form
     * keeps whatever it has, forever; a brand new site gets a working one.
     */
    private function seedForms($console): void
    {
        $seeds = base_path('resources/form-seeds');

        if (! is_dir($seeds)) {
            return;
        }

        // Guard against a future export_paths change silently re-introducing
        // the overwrite: if the kit ever ships the live paths again, the copy
        // below is pointless and the site's settings are already gone.
        foreach (['forms', 'blueprints/forms'] as $shipped) {
            if (is_dir(base_path("vendor/floorplate/fp-template-sk-export/export/resources/{$shipped}"))) {
                $console->warn(
                    "Starter kit ships resources/{$shipped}; per-site form settings are being overwritten on every deploy. "
                    .'Remove it from export_paths in package/starter-kit.yaml.'
                );
            }
        }

        $seeded = 0;

        foreach ([
            'forms' => 'resources/forms',
            'blueprints' => 'resources/blueprints/forms',
        ] as $from => $to) {
            foreach (glob("{$seeds}/{$from}/*.yaml") ?: [] as $seed) {
                $destination = base_path($to.'/'.basename($seed));

                if (file_exists($destination)) {
                    continue;
                }

                if (! is_dir(dirname($destination))) {
                    mkdir(dirname($destination), 0755, true);
                }

                copy($seed, $destination);
                $seeded++;

                $console->line('Seeding form file ['.$to.'/'.basename($seed).']');
            }
        }

        if ($seeded === 0) {
            $console->line('Forms already present; leaving this site\'s form settings untouched.');
        }
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
