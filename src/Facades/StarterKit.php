<?php

namespace Fligno\StarterKit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static void enableRepositoryPattern(string $path)
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
