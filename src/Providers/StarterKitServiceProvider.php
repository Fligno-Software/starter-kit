<?php

namespace Fligno\StarterKit\Providers;

use Fligno\StarterKit\Abstracts\BaseStarterKitServiceProvider as ServiceProvider;
use Fligno\StarterKit\Exceptions\Handler;
use Fligno\StarterKit\Services\CustomResponse;
use Fligno\StarterKit\Services\PackageDomain;
use Fligno\StarterKit\Services\StarterKit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;

/**
 * Class StarterKitServiceProvider
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
class StarterKitServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected array $commands = [];

    /**
     * Publishable Environment Variables
     *
     * @example [ 'SK_OVERRIDE_EXCEPTION_HANDLER' => true ]
     *
     * @var array
     */
    protected array $env_vars = [
        'SK_OVERRIDE_EXCEPTION_HANDLER' => false,
        'SK_ENFORCE_MORPH_MAP' => true,
        'SK_VERIFY_SSL' => true,
        'SK_SENTRY_ENABLED' => false,
        'SK_SENTRY_TEST_API_ENABLED' => false,
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

            starterKit()->addExceptionRender(ModelNotFoundException::class, function () {
                return customResponse()
                    ->data([])
                    ->message('The identifier you are querying does not exist.')
                    ->slug('no_query_result')
                    ->failed(404)
                    ->generate();
            });

            starterKit()->addExceptionRender(AuthorizationException::class, function () {
                return customResponse()
                    ->data([])
                    ->message('You do not have right to access this resource.')
                    ->slug('forbidden_request')
                    ->failed(403)
                    ->generate();
            });
        }

        // Register custom migration functions
        Blueprint::macro('expires', function (string $column = 'expires_at') {
            $this->timestamp($column)->nullable();
        });

        Blueprint::macro('disables', function (string $column = 'disabled_at') {
            $this->timestamp($column)->nullable();
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/starter-kit.php', 'starter-kit');

        $this->app->singleton('starter-kit', fn () => new StarterKit());

        $this->app->singleton('package-domain', function (Application $app) {
            return new PackageDomain(
                app: $app,
                starter_kit: $app->make('starter-kit'),
                config: $app->make('config'),
                migrator: $app->make('migrator'),
                view: $app->make('view'),
                translator: $app->make('translator'),
            );
        });

        $this->app->bind('custom-response', fn () => new CustomResponse());

        parent::register();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['starter-kit', 'custom-response', 'package-domain'];
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
     * @return string|null
     */
    public function getRoutePrefix(): ?string
    {
        return 'starter-kit';
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
