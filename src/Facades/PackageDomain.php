<?php

namespace Fligno\StarterKit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class PackageDomain
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 *
 * @see \{{ namespacedContainer }}
 */
class PackageDomain extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return '{{ containerSlug }}';
    }
}
