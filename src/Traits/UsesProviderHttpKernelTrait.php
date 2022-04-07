<?php

namespace Fligno\StarterKit\Traits;

use Fligno\StarterKit\Interfaces\ProviderHttpKernelInterface;

/**
 * Trait UsesProviderHttpKernelTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesProviderHttpKernelTrait
{
    /**
     * @return void
     */
    public function bootHttpKernel(): void
    {
        if ($this instanceof ProviderHttpKernelInterface) {
            app()->booted(
                function () {
                    if (method_exists($this, 'registerToHttpKernel')) {
                        $this->registerToHttpKernel(app('router'));
                    }
                }
            );
        }
    }
}
