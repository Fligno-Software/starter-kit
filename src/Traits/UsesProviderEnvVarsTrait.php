<?php

namespace Fligno\StarterKit\Traits;

/**
 * Trait UsesProviderEnvVarsTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesProviderEnvVarsTrait
{
    /**
     * Publishable Environment Variables
     *
     * @link    https://laravel.com/docs/8.x/eloquent#observers
     *
     * @example [ 'SK_OVERRIDE_EXCEPTION_HANDLER' => true ]
     *
     * @var array
     */
    protected array $env_vars = [];

    /**
     * @return array
     */
    public function getEnvVars(): array
    {
        return $this->env_vars;
    }
}
