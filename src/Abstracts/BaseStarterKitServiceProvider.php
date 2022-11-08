<?php

namespace Fligno\StarterKit\Abstracts;

use Fligno\StarterKit\Traits\UsesProviderStarterKitTrait;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Class BaseStarterKitServiceProvider
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
abstract class BaseStarterKitServiceProvider extends ServiceProvider
{
    use UsesProviderStarterKitTrait;

    /**
     * Create a new service provider instance.
     *
     * @param  Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->instantiatePackageDomain();
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Boot Laravel Files
        $this->getPackageDomain()->bootLaravelFiles();

        // For Console Kernel
        $this->bootConsoleKernel();

        // For Http Kernel
        $this->bootHttpKernel();

        // For Dynamic Relationships
        $this->bootDynamicRelationships();

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Initialize Package Domain
        if ($this->getPackageDomain()->initialize()) {
            // Register Laravel Files
            $this->getPackageDomain()->registerLaravelFiles();
        }
    }
}
