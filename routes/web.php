<?php

use Illuminate\Support\Facades\Route;
use Jawabapp\RemoteConfig\Http\Controllers\FlowController;
use Jawabapp\RemoteConfig\Http\Controllers\ExperimentController;
use Jawabapp\RemoteConfig\Http\Controllers\WinnerController;
use Jawabapp\RemoteConfig\Http\Controllers\TestingController;

/*
|--------------------------------------------------------------------------
| Remote Config Admin Routes
|--------------------------------------------------------------------------
|
| These routes provide the admin panel interface for managing flows,
| experiments, winners, and testing overrides.
|
*/

$webConfig = config('remote-config.routes', []);

if ($webConfig['enabled'] ?? true) {
    Route::prefix($webConfig['prefix'] ?? 'remote-config')
        ->middleware($webConfig['middleware'] ?? ['web', 'auth'])
        ->name(($webConfig['as'] ?? 'remote-config.'))
        ->group(function () {
            // Dashboard
            Route::get('/', function () {
                return redirect()->route('remote-config.flows.index');
            })->name('dashboard');

            // Flow Management
            Route::resource('flows', FlowController::class);
            Route::post('flows/{flow}/toggle', [FlowController::class, 'toggle'])->name('flows.toggle');

            // Experiment Management
            Route::resource('experiments', ExperimentController::class);
            Route::post('experiments/{experiment}/toggle', [ExperimentController::class, 'toggle'])->name('experiments.toggle');
            Route::post('experiments/{experiment}/attach-flow', [ExperimentController::class, 'attachFlow'])->name('experiments.attach-flow');
            Route::delete('experiments/{experiment}/detach-flow/{flow}', [ExperimentController::class, 'detachFlow'])->name('experiments.detach-flow');
            Route::get('experiments/{experiment}/stats', [ExperimentController::class, 'stats'])->name('experiments.stats');

            // Winner Management
            Route::resource('winners', WinnerController::class);
            Route::post('winners/{winner}/toggle', [WinnerController::class, 'toggle'])->name('winners.toggle');

            // Testing Override Management
            Route::get('testing', [TestingController::class, 'index'])->name('testing.index');
            Route::post('testing', [TestingController::class, 'store'])->name('testing.store');
            Route::delete('testing/{ip}/{type}', [TestingController::class, 'destroy'])->name('testing.destroy');
            Route::delete('testing', [TestingController::class, 'clear'])->name('testing.clear');
        });
}
