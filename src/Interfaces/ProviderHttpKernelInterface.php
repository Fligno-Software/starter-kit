<?php

namespace Fligno\StarterKit\Interfaces;

use Illuminate\Routing\Router;

/**
 * Interface HttpKernelInterface
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
interface ProviderHttpKernelInterface
{
    /**
     * @param Router $router
     * @return void
     */
    public function registerToHttpKernel(Router $router): void;
}
