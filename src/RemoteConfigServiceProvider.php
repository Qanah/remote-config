<?php

namespace Jawabapp\RemoteConfig;

use Illuminate\Support\ServiceProvider;
use Jawabapp\RemoteConfig\Models\Flow;
use Jawabapp\RemoteConfig\Models\Experiment;
use Jawabapp\RemoteConfig\Models\Winner;
use Jawabapp\RemoteConfig\Models\ExperimentAssignment;
use Jawabapp\RemoteConfig\Observers\FlowObserver;
use Jawabapp\RemoteConfig\Observers\ExperimentObserver;
use Jawabapp\RemoteConfig\Observers\WinnerObserver;
use Jawabapp\RemoteConfig\Observers\ExperimentAssignmentObserver;
use Jawabapp\RemoteConfig\Services\ExperimentService;
use Jawabapp\RemoteConfig\Services\ConfigService;

class RemoteConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/remote-config.php',
            'remote-config'
        );

        // Register services
        $this->app->singleton(ExperimentService::class, function ($app) {
            return new ExperimentService();
        });

        $this->app->singleton(ConfigService::class, function ($app) {
            return new ConfigService($app->make(ExperimentService::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'remote-config');

        // Register observers
        if (config('remote-config.audit_logging.enabled', true)) {
            Flow::observe(FlowObserver::class);
            Experiment::observe(ExperimentObserver::class);
            Winner::observe(WinnerObserver::class);
        }

        if (config('remote-config.audit_logging.log_assignments', true)) {
            ExperimentAssignment::observe(ExperimentAssignmentObserver::class);
        }

        // Publishing
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/remote-config.php' => config_path('remote-config.php'),
            ], 'remote-config-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'remote-config-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/remote-config'),
            ], 'remote-config-views');

            $this->publishes([
                __DIR__ . '/../public' => public_path('vendor/remote-config'),
            ], 'remote-config-assets');
        }
    }
}
