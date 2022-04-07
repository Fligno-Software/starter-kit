<?php

namespace Fligno\StarterKit\Interfaces;

use Illuminate\Console\Scheduling\Schedule;

/**
 * Interface ConsoleKernelInterface
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
interface ProviderConsoleKernelInterface
{
    /**
     * @param  Schedule $schedule
     * @return void
     */
    public function registerToConsoleKernel(Schedule $schedule): void;
}
