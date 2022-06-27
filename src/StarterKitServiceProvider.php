<?php

namespace Fligno\StarterKit;

use Fligno\StarterKit\Console\Commands\StarterKitClearCacheCommand;
use Fligno\StarterKit\Console\Commands\StarterKitGitHooksApplyCommand;
use Fligno\StarterKit\Console\Commands\StarterKitGitHooksRemoveCommand;
use Fligno\StarterKit\Exceptions\Handler;
use Fligno\StarterKit\Providers\BaseStarterKitServiceProvider as ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;

class StarterKitServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected array $commands = [
        StarterKitClearCacheCommand::class,
        StarterKitGitHooksApplyCommand::class,
        StarterKitGitHooksRemoveCommand::class,
    ];

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        // Register Custom Exception Handler
        if (config('starter-kit.override_exception_handler')) {
            $this->app->singleton(ExceptionHandler::class, Handler::class);
        }
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
        $this->app->singleton(
            'starter-kit',
            function () {
                return new StarterKit();
            }
        );

        // Register the service the package provides.
        $this->app->bind(
            'extended-response',
            function () {
                return new ExtendedResponse();
            }
        );

        parent::register();
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
        $this->publishes(
            [
            __DIR__.'/../config/starter-kit.php' => config_path('starter-kit.php'),
            ],
            'starter-kit.config'
        );

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
        $this->commands($this->commands);
    }

    /**
     * @return bool
     */
    public function areHelpersEnabled(): bool
    {
        return false;
    }
}
