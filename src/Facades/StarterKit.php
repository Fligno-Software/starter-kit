<?php

namespace Fligno\StarterKit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static void registerRepositories(string $repositoriesPath = null, string $modelsPath = null)
 * @method static void registerPolicies(string $policiesPath = null, string $modelsPath = null)
 * @method static void registerObservers(string $observersPath = null, string $modelsPath = null)
 *
 * @see \Fligno\StarterKit\StarterKit
 */
class StarterKit extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'starter-kit';
    }
}
