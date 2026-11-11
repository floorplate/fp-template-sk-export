<?php

use Floorplate\FleetAgent\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

// The dashboard widget's data, fetched from Fleet on the server so the
// browser never sees the signing secret.
Route::get('fleet-agent/analytics/{view}', [AnalyticsController::class, 'show'])
    ->whereIn('view', AnalyticsController::VIEWS)
    ->name('fleet-agent.analytics');
