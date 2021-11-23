<?php

namespace Fligno\StarterKit;

use Fligno\StarterKit\Exceptions\Handler;
use Fligno\StarterKit\Macros\ArrMacros;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use ReflectionException;

class StarterKitServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     * @throws ReflectionException
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'fligno');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'fligno');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        // Register Custom Exception Handler
        if (config('boilerplate-generator.override_exception_handler')) {
            $this->app->singleton(ExceptionHandler::class, Handler::class);
        }

        // Boot Arr
        Arr::mixin(new ArrMacros);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/starter-kit.php', 'starter-kit');

        // Register the service the package provides.
        $this->app->singleton('starter-kit', function ($app) {
            return new StarterKit;
        });

        // Register the service the package provides.
        $this->app->bind('extended-response', function ($app) {
            return new ExtendedResponse();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['starter-kit', 'extended-response'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/starter-kit.php' => config_path('starter-kit.php'),
        ], 'starter-kit.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/fligno'),
        ], 'starter-kit.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/fligno'),
        ], 'starter-kit.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/fligno'),
        ], 'starter-kit.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
