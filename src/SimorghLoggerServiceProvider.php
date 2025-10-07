<?php

namespace Simorgh\Logger;

use Simorgh\Logger\Commands\CleanupLogsCommand;
use Simorgh\Logger\Middleware\LogRequestsMiddleware;
use Simorgh\Logger\Models\LogEntry;
use Simorgh\Logger\Observers\LogModelObserver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SimorghLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/advanced-logger.php', 'advanced-logger');
        
        $this->app->singleton(Contracts\LoggerInterface::class, function (Application $app) {
            return new Services\LoggerService($app);
        });
        
        $this->app->alias(Contracts\LoggerInterface::class, 'advanced-logger');
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__.'/../config/advanced-logger.php' => config_path('advanced-logger.php'),
        ], 'advanced-logger-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'advanced-logger-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/advanced-logger'),
        ], 'advanced-logger-views');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'advanced-logger');

        // Load routes
        $this->loadRoutes();

        // Register middleware
        $this->registerMiddleware();

        // Register commands
        $this->registerCommands();

        // Setup model observers
        $this->setupModelObservers();
    }

    /**
     * Load package routes.
     */
    protected function loadRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        if (!config('advanced-logger.dashboard.enabled', true)) {
            return;
        }

        $prefix = config('advanced-logger.dashboard.prefix', 'advanced-logger');
        $middleware = config('advanced-logger.dashboard.middleware', ['web', 'auth']);

        Route::middleware($middleware)
            ->prefix($prefix)
            ->group(__DIR__.'/../routes/web.php');
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        
        $router->aliasMiddleware('log.requests', LogRequestsMiddleware::class);
        $router->aliasMiddleware('advanced.logger', LogRequestsMiddleware::class);
    }

    /**
     * Register package commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupLogsCommand::class,
            ]);
        }
    }

    /**
     * Setup model observers.
     */
    protected function setupModelObservers(): void
    {
        if (config('advanced-logger.auto_observe_models', true)) {
            // Auto-observe common models
            $models = config('advanced-logger.observed_models', []);
            
            foreach ($models as $model) {
                if (class_exists($model)) {
                    $model::observe(LogModelObserver::class);
                }
            }
        }
    }
}
