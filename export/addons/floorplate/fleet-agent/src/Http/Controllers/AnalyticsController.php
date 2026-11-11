<?php

namespace Floorplate\FleetAgent\Http\Controllers;

use Floorplate\FleetAgent\Support\FleetClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * The dashboard widget's figures: forwarded to Fleet (FleetClient) and back.
 * A CP route, so only people who can use the CP see them.
 */
class AnalyticsController extends Controller
{
    public const VIEWS = ['report', 'events', 'event', 'live'];

    public function show(Request $request, FleetClient $fleet, string $view): JsonResponse
    {
        $query = array_filter([
            'range' => is_string($range = $request->query('range')) ? $range : null,
            'name' => $view === 'event' && is_string($name = $request->query('name')) ? mb_substr($name, 0, 255) : null,
        ]);

        $result = $fleet->analytics($view, $query);

        return response()->json($result['data'], $result['status'])->header('Cache-Control', 'no-store, private');
    }
}
