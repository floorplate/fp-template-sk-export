<?php

namespace Floorplate\FloorplateStarterKit\StaticCaching;

use Illuminate\Http\Request;
use Statamic\StaticCaching\Cachers\ApplicationCacher as StatamicApplicationCacher;
use Symfony\Component\HttpFoundation\Response;

/**
 * The half-measure cacher, minus one entry per error URL.
 *
 * Statamic's application cacher stores 404s under their own URL. Vulnerability
 * scanners probe thousands of unique paths (/backup_2026-04.sql.gz, /secrets.tar,
 * ...), so every probe was a guaranteed cache miss that rendered the full 404 and
 * then added another entry to the cache and its URL index.
 *
 * Error responses are only kept under Statamic's shared-error key
 * (/__shared-errors/{site}/{status}), which `share_errors` serves for every URL
 * with that status. Visitors still get the styled 404; the cache holds one copy.
 */
class ApplicationCacher extends StatamicApplicationCacher
{
    public function cachePage(Request $request, $content)
    {
        if ($this->isErrorResponse($content) && ! $this->isSharedErrorRequest($request)) {
            return;
        }

        parent::cachePage($request, $content);
    }

    private function isErrorResponse($content): bool
    {
        return $content instanceof Response && $content->getStatusCode() >= 400;
    }

    private function isSharedErrorRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/__shared-errors/');
    }
}
