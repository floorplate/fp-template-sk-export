<?php

namespace Floorplate\StillStreamAgent\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\AssetContainer;
use Throwable;

/**
 * A project's files in this site's asset container, under stillstream/.
 * Photos go in through Statamic like any upload. What Floorplate generates
 * is written straight into the bucket, so it has to be added to the
 * container's listing before editors can see it.
 */
class ProjectAssets
{
    public const CONTAINER = 'assets';

    /** @param  'uploads'|'fonts'  $folder */
    public function upload(string $projectUuid, UploadedFile $file, string $folder = 'uploads'): Asset
    {
        $name = (Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file')
            .'-'.Str::lower(Str::random(6)).'.'.strtolower($file->getClientOriginalExtension() ?: $file->extension());

        return AssetContainer::find(self::CONTAINER)
            ->makeAsset("stillstream/{$projectUuid}/{$folder}/{$name}")
            ->upload($file);
    }

    /**
     * Add files Floorplate wrote into the bucket to the container.
     *
     * @param  list<string>  $paths
     */
    public function register(array $paths): void
    {
        $container = AssetContainer::find(self::CONTAINER);

        foreach ($paths as $path) {
            // Only ever our own folder, whatever the response says.
            if (! str_starts_with($path, 'stillstream/') || str_contains($path, '..') || $container->asset($path)) {
                continue;
            }

            try {
                if (! $container->disk()->exists($path)) {
                    continue;
                }

                // The listing is cached; until the path is in it the asset
                // does not exist as far as Statamic is concerned.
                $container->contents()->add($path)->save();

                $asset = $container->makeAsset($path);
                $asset->cacheStore()->forget($asset->metaCacheKey());
                $asset->hydrate()->save();
            } catch (Throwable $e) {
                // The video still plays from its URL; it is only missing
                // from the asset browser, and the next visit tries again.
                report($e);
            }
        }
    }
}
