<?php

namespace Fligno\StarterKit\Providers;

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
     * @param  Application $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->setProviderStarterKitFields();
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->bootLaravelFilesAndDomains();
    }
}
