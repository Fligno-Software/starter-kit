<?php

namespace Fligno\StarterKit\Traits;

use Fligno\StarterKit\Interfaces\ProviderConsoleKernelInterface;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Trait UsesProviderConsoleKernelTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesProviderConsoleKernelTrait
{
    /**
     * @return void
     */
    public function bootConsoleKernel(): void
    {
        if ($this instanceof ProviderConsoleKernelInterface) {
            app()->booted(function () {
                if (method_exists($this, 'registerToConsoleKernel')) {
                    $this->registerToConsoleKernel(app(Schedule::class));
                }
            });
        }
    }
}
