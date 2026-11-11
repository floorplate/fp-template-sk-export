<?php

namespace Floorplate\FleetAgent\Widgets;

use Floorplate\FleetAgent\Http\Controllers\AnalyticsController;
use Floorplate\FleetAgent\Support\FleetClient;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;

/**
 * The site's Fathom analytics on the CP dashboard: visitors, page views, time
 * on site and bounce rate against the previous period, the trend, top pages,
 * where visitors came from, where they are and what they use, live visitors,
 * and the click events the kit tracks.
 *
 * Shown only once analytics are on (a Fathom Site ID in Globals →
 * Integrations) and the site is connected to Fleet, which reads the figures
 * from Fathom; otherwise the widget isn't drawn at all.
 */
class FathomAnalytics extends Widget
{
    public function component()
    {
        $integrations = GlobalSet::findByHandle('integrations')?->inDefaultSite();

        if (! $integrations?->get('fathom_site_id') || ! FleetClient::configured()) {
            return null;
        }

        return VueComponent::render('fathom-analytics-widget', [
            'title' => $this->config('title', __('Analytics')),
            'defaultRange' => $this->config('range', '30d'),
            'urls' => collect(AnalyticsController::VIEWS)
                ->mapWithKeys(fn (string $view) => [$view => cp_route('fleet-agent.analytics', ['view' => $view])])
                ->all(),
            'siteUrl' => rtrim(Site::default()->absoluteUrl(), '/'),
            'settingsUrl' => cp_route('globals.variables.edit', 'integrations'),
            'eventsOn' => $integrations->get('fathom_events') !== false,
        ]);
    }
}
