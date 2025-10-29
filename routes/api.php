<?php

use Illuminate\Support\Facades\Route;
use Jawabapp\RemoteConfig\Http\Controllers\Api\ConfigController;

/*
|--------------------------------------------------------------------------
| Remote Config API Routes
|--------------------------------------------------------------------------
|
| These routes handle client-facing API requests for remote configuration,
| experiments, winners, and test overrides.
|
*/

$apiConfig = config('remote-config.api_routes', []);

if ($apiConfig['enabled'] ?? true) {
    Route::prefix($apiConfig['prefix'] ?? 'api/config')
        ->middleware($apiConfig['middleware'] ?? ['api'])
        ->name(($apiConfig['as'] ?? 'remote-config.api.'))
        ->group(function () {
            // Get remote configuration
            Route::get('/', [ConfigController::class, 'index'])->name('index');

            // Confirm experiment completion
            Route::post('/confirm', [ConfigController::class, 'confirm'])->name('confirm');

            // Report validation issue
            Route::post('/issue', [ConfigController::class, 'reportIssue'])->name('issue');

            // Get test flow (for QA/testing)
            Route::get('/testing', [ConfigController::class, 'testingFlow'])->name('testing');
        });
}
