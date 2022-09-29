<?php

namespace Fligno\StarterKit\Providers;

use Fligno\StarterKit\Console\Commands\StarterKitClearCacheCommand;
use Fligno\StarterKit\Console\Commands\StarterKitGitHooksApplyCommand;
use Fligno\StarterKit\Console\Commands\StarterKitGitHooksPublishCommand;
use Fligno\StarterKit\Console\Commands\StarterKitGitHooksRemoveCommand;
use Fligno\StarterKit\Exceptions\Handler;
use Fligno\StarterKit\Providers\BaseStarterKitServiceProvider as ServiceProvider;
use Fligno\StarterKit\Services\ExtendedResponse;
use Fligno\StarterKit\Services\StarterKit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class StarterKitServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected array $commands = [
        StarterKitClearCacheCommand::class,
        StarterKitGitHooksApplyCommand::class,
        StarterKitGitHooksRemoveCommand::class,
        StarterKitGitHooksPublishCommand::class,
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
        if (starterKit()->shouldOverrideExceptionHandler()) {
            $this->app->singleton(ExceptionHandler::class, Handler::class);

            starterKit()->addExceptionRender(ModelNotFoundException::class, function (Throwable $e) {
                return customResponse()
                    ->data([])
                    ->message('The identifier you are querying does not exist.')
                    ->slug('no_query_result')
                    ->failed(404)
                    ->generate();
            });

            starterKit()->addExceptionRender(AuthorizationException::class, function (Throwable $e) {
                return customResponse()
                    ->data([])
                    ->message('You do not have right to access this resource.')
                    ->slug('forbidden_request')
                    ->failed(403)
                    ->generate();
            });
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/starter-kit.php', 'starter-kit');
        $this->mergeConfigFrom(__DIR__ . '/../../config/git-hooks.php', 'git-hooks');

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

//        parent::register();
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
                __DIR__ . '/../config/starter-kit.php' => config_path('starter-kit.php'),
            ],
            'starter-kit.config'
        );

        $this->publishes(
            [
                __DIR__ . '/../config/git-hooks.php' => config_path('git-hooks.php'),
            ],
            'git-hooks.config'
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
     * @return string|null
     */
    public function getRoutePrefix(): ?string
    {
        return 'starter-kit';
    }

    /**
     * @param  bool  $is_api
     * @return array
     */
    public function getDefaultRouteMiddleware(bool $is_api): array
    {
        return [];
    }

    /**
     * @return bool
     */
    public function areHelpersEnabled(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function areConfigsEnabled(): bool
    {
        return false;
    }
}
