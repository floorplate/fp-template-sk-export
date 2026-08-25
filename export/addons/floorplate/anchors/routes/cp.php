<?php

use Floorplate\Anchors\Http\Controllers\EntryAnchorsController;
use Illuminate\Support\Facades\Route;

// Wrapped by Statamic in the CP route group: '/cp' URI prefix, 'statamic.cp.'
// name prefix, and CP auth middleware. The param is {entryId}, NOT {entry} —
// Statamic globally route-model-binds {entry} to an Entry object (see
// RouteServiceProvider::bind('entry')), which would break the manual lookup.

Route::name('anchors.')->prefix('anchors')->group(function () {
    Route::get('entries/{entryId}', [EntryAnchorsController::class, 'show'])
        ->name('entries.show');
});
