<?php

use Floorplate\StillStreamAgent\Http\Controllers\ProjectsController;
use Floorplate\StillStreamAgent\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// StillStream runs on Floorplate; these pages forward to it from the server,
// so the browser never sees the signing secret. Floorplate checks the same
// permissions again on its side.
Route::name('stillstream-agent.')->prefix('stillstream')->middleware('can:access stillstream')->group(function () {
    Route::get('projects', [ProjectsController::class, 'index'])->name('projects.index');
    Route::post('projects', [ProjectsController::class, 'store'])->name('projects.store')->can('create stillstream projects');
    Route::get('projects/{project}', [ProjectsController::class, 'show'])->name('projects.show')->whereNumber('project');
    Route::get('projects/{project}/editor', [ProjectsController::class, 'editor'])->name('projects.editor')->whereNumber('project');
    Route::put('projects/{project}/document', [ProjectsController::class, 'saveDocument'])->name('projects.document')->whereNumber('project')->can('create stillstream projects');
    Route::patch('projects/{project}', [ProjectsController::class, 'update'])->name('projects.update')->whereNumber('project')->can('create stillstream projects');
    Route::delete('projects/{project}', [ProjectsController::class, 'destroy'])->name('projects.destroy')->whereNumber('project')->can('create stillstream projects');
    Route::post('projects/{project}/photos', [ProjectsController::class, 'upload'])->name('projects.upload')->whereNumber('project')->can('create stillstream projects');
    Route::post('projects/{project}/fonts', [ProjectsController::class, 'font'])->name('projects.fonts')->whereNumber('project')->can('create stillstream projects');
    Route::post('projects/{project}/clips', [ProjectsController::class, 'clip'])->name('projects.clip')->whereNumber('project')->can('generate stillstream media');
    Route::post('projects/{project}/audio', [ProjectsController::class, 'audio'])->name('projects.audio')->whereNumber('project')->can('generate stillstream media');
    Route::post('projects/{project}/renders', [ProjectsController::class, 'render'])->name('projects.render')->whereNumber('project')->can('render stillstream videos');

    Route::get('credits', [WalletController::class, 'show'])->name('wallet');
    Route::post('credits/purchase', [WalletController::class, 'purchase'])->name('wallet.purchase')->can('purchase stillstream credits');
});
