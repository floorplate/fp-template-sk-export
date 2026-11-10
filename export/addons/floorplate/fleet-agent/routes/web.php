<?php

use Floorplate\FleetAgent\Http\Controllers\FleetLoginController;
use Illuminate\Support\Facades\Route;

// Paths are fixed: Fleet builds them (FleetSsoLink) without knowing anything
// about the site. Pushed into Statamic's web group, so they win over the
// front-end catch-all and get sessions.
Route::get('!/fleet/login', [FleetLoginController::class, 'login'])->name('fleet-agent.login');
Route::get('!/fleet/sso/check', [FleetLoginController::class, 'check'])->name('fleet-agent.check');
