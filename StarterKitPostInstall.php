<?php

use Statamic\Console\Processes\Composer;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Wires the bundled addons (Anchors, Fleet Agent) into the freshly installed site.
 *
 * Each addon ships as files under addons/floorplate/anchors rather than as a
 * Packagist package, so it cannot be listed in starter-kit.yaml's
 * `dependencies` — the installer dry-runs those against Packagist and would
 * abort. Instead we register a local path repository and require it here.
 *
 * Safe to re-run: the repository entry is only added when missing, and
 * `composer require` on an already-installed package is a no-op.
 */
class StarterKitPostInstall
{
    private const PATH = 'addons/floorplate/anchors';

    /**
     * Local addons the kit ships under addons/, as package => path.
     * Each also needs its files listed in starter-kit.yaml's export_paths.
     */
    private const ADDONS = [
        'floorplate/anchors' => self::PATH,
        // One-click CP login from Fleet with a per-site managed admin, instead
        // of personal accounts shared across every site.
        'floorplate/fleet-agent' => 'addons/floorplate/fleet-agent',
    ];

    /**
     * Written when starter content is seeded. Its presence means "this site
     * has had its starter content"; it's committed with the rest of content/.
     */
    private const STARTER_CONTENT_MARKER = 'content/.starter-content-installed';

    /**
     * The SEO Pro settings every kit before resources/addon-seeds shipped at the
     * live path, and so wrote over each site's own on every install.
     */
    private const LEGACY_SEO_PRO_SETTINGS = [
        'site_defaults' => [
            'title' => '@seo:title',
            'description' => "An experiential agency. We deliver groundbreaking ideas and meaningful brand experiences. We don't help brands “catch up” we help brands push forward.",
            'site_name' => 'Futureman Digital',
            'site_name_position' => 'after',
            'site_name_separator' => '|',
            'canonical_url' => '@seo:permalink',
            'image' => false,
            'priority' => 0.5,
            'change_frequency' => 'monthly',
        ],
    ];

    public function handle($console): void
    {
        $this->seedForms($console);
        $this->seedStarterContent($console);
        $this->seedAddonSettings($console);
        $this->ensureIntegrationsGlobal($console);

        foreach (self::ADDONS as $package => $path) {
            $this->installAddon($console, $package, $path);
        }

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
     * Gives a brand new site its starter content, once: the welcome homepage,
     * the demo page, the availability examples, a starter nav and the
     * Floorplate theme.
     *
     * This hook runs on every kit update as well as the first install, and
     * content belongs to the site from the moment it exists: editors rewrite
     * the homepage, delete the demo, build their own navigation. So no content/
     * path is in export_paths. The kit carries resources/content-seeds instead,
     * mirroring content/, and we copy from it only on a site that has never
     * had starter content. That means no marker file and no pages at all. Even
     * then a file that already exists is never replaced.
     *
     * The marker is what makes this permanent. Without it, a site whose editors
     * deleted every page would get the demo back on the next kit update.
     */
    private function seedStarterContent($console): void
    {
        $seeds = base_path('resources/content-seeds');

        if (! is_dir($seeds)) {
            return;
        }

        if (file_exists(base_path(self::STARTER_CONTENT_MARKER))) {
            $console->line('Starter content already installed; leaving this site\'s content untouched.');

            return;
        }

        if (glob(base_path('content/collections/pages/*.md'))) {
            $console->line('Site already has pages; skipping starter content.');

            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($seeds, FilesystemIterator::SKIP_DOTS)
        );

        $seeded = 0;

        foreach ($files as $seed) {
            if (! $seed->isFile()) {
                continue;
            }

            $relative = ltrim(substr($seed->getPathname(), strlen($seeds)), DIRECTORY_SEPARATOR);
            $destination = base_path('content/'.$relative);

            if (file_exists($destination)) {
                continue;
            }

            if (! is_dir(dirname($destination))) {
                mkdir(dirname($destination), 0755, true);
            }

            copy($seed->getPathname(), $destination);
            $seeded++;
        }

        file_put_contents(
            base_path(self::STARTER_CONTENT_MARKER),
            "Floorplate starter content was installed on this site.\n"
            ."While this file exists, starter kit installs and updates never add starter pages again.\n"
        );

        $console->line("Seeded {$seeded} starter content file(s).");
    }

    /**
     * Installs addon settings (SEO Pro's site defaults) into a site without them.
     *
     * Like forms, these are the site's own: the CP writes SEO → Site Defaults
     * into resources/addons/seo-pro.yaml. So the kit ships them as
     * resources/addon-seeds and copies each one only where the site has none.
     *
     * The one exception: a file that is still exactly what older kits shipped
     * (the Futureman Digital defaults) is replaced. Those kits overwrote it on
     * every install, so on those sites it holds nothing an editor chose.
     */
    private function seedAddonSettings($console): void
    {
        foreach (glob(base_path('resources/addon-seeds/*.yaml')) ?: [] as $seed) {
            $destination = base_path('resources/addons/'.basename($seed));

            if (file_exists($destination) && ! $this->isLegacySeoProSettings($seed, $destination)) {
                continue;
            }

            if (! is_dir(dirname($destination))) {
                mkdir(dirname($destination), 0755, true);
            }

            copy($seed, $destination);

            $console->line('Seeding addon settings [resources/addons/'.basename($seed).']');
        }
    }

    private function isLegacySeoProSettings(string $seed, string $destination): bool
    {
        if (basename($seed) !== 'seo-pro.yaml') {
            return false;
        }

        try {
            return Yaml::parseFile($destination) === self::LEGACY_SEO_PRO_SETTINGS;
        } catch (ParseException) {
            return false;
        }
    }

    /**
     * Gives every site the Integrations global set (Fathom, Realty Ads) and
     * moves an older kit's Analytics set into it.
     *
     * The set's own file lives in content/, which the kit never ships, so it
     * is created here when missing; the blueprint ships normally. Kits before
     * Integrations called it Analytics, and Fleet may already have saved a
     * Fathom ID there. That set's values move across (values already in
     * Integrations win), then its files and the old blueprint are removed:
     * the installer only ever adds files, so nothing else would delete them,
     * and the CP would list both sets. Fleet commits content/globals with the
     * kit update.
     */
    private function ensureIntegrationsGlobal($console): void
    {
        $globals = base_path('content/globals');
        $changed = false;

        if (! is_dir($globals)) {
            mkdir($globals, 0755, true);
        }

        if (! file_exists("{$globals}/integrations.yaml")) {
            file_put_contents("{$globals}/integrations.yaml", "title: Integrations\n");
            $console->line('Created Globals → Integrations.');
            $changed = true;
        }

        if (file_exists("{$globals}/analytics.yaml")) {
            $legacy = $this->readYaml("{$globals}/analytics.yaml");

            // A Statamic 5 style set keeps its values in the set file itself.
            if (is_array($legacy['data'] ?? null) && $legacy['data']) {
                $this->mergeGlobalVariables("{$globals}/".Site::default()->handle().'/integrations.yaml', $legacy['data']);
            }

            foreach (glob("{$globals}/*/analytics.yaml") ?: [] as $variables) {
                $this->mergeGlobalVariables(dirname($variables).'/integrations.yaml', $this->readYaml($variables));
                unlink($variables);
            }

            unlink("{$globals}/analytics.yaml");
            $console->line('Moved Globals → Analytics into Globals → Integrations.');
            $changed = true;
        }

        if (file_exists($blueprint = base_path('resources/blueprints/globals/analytics.yaml'))) {
            unlink($blueprint);
            $changed = true;
        }

        if ($changed) {
            Stache::clear();
        }
    }

    /** Adds values to a set's per-site variables file without replacing any already there. */
    private function mergeGlobalVariables(string $file, array $values): void
    {
        $current = file_exists($file) ? $this->readYaml($file) : [];
        $merged = $current + array_filter($values, fn ($value) => $value !== null && $value !== '');

        if ($merged === $current) {
            return;
        }

        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        file_put_contents($file, Yaml::dump($merged, 99, 2));
    }

    private function readYaml(string $file): array
    {
        try {
            $data = Yaml::parseFile($file);
        } catch (ParseException) {
            return [];
        }

        return is_array($data) ? $data : [];
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

    private function installAddon($console, string $package, string $path): void
    {
        if (! is_dir(base_path($path))) {
            $console->warn("Addon [{$package}] not found at [{$path}]; skipping.");

            return;
        }

        if (! $this->registerPathRepository($console, $path)) {
            return;
        }

        $console->line("Installing addon [{$package}]");

        // Resolved from the container rather than via Statamic's real-time
        // `Facades\` alias, which is not reliably autoloadable from a hook file
        // that the installer pulls in with require_once.
        app(Composer::class)->withoutQueue()->throwOnFailure()->require($package, '*');
    }

    private function registerPathRepository($console, string $path): bool
    {
        $file = base_path('composer.json');
        $json = json_decode(file_get_contents($file), true);

        if (! is_array($json)) {
            $console->error("Could not read composer.json; skipping addon setup for [{$path}].");

            return false;
        }

        $repositories = $json['repositories'] ?? [];

        foreach ($repositories as $repository) {
            if (($repository['url'] ?? null) === $path) {
                return true;
            }
        }

        $repositories[] = [
            'type' => 'path',
            'url' => $path,
            'options' => ['symlink' => true],
        ];

        $json['repositories'] = $repositories;

        file_put_contents(
            $file,
            json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
        );

        return true;
    }
}
