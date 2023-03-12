<?php

namespace Fligno\StarterKit\Abstracts;

use Fligno\StarterKit\Traits\UsesProviderStarterKitTrait;
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
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Boot Laravel Files
        packageDomain()->provider($this)
            ->registerLaravelFiles()
            ->bootLaravelFiles();

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
}
