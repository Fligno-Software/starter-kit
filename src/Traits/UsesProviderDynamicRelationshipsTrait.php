<?php

namespace Fligno\StarterKit\Traits;

use Fligno\StarterKit\Interfaces\ProviderDynamicRelationshipsInterface;

/**
 * Trait UsesProviderDynamicRelationshipsTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesProviderDynamicRelationshipsTrait
{
    /**
     * @return void
     *
     * @link   https://laravel.com/docs/8.x/eloquent-relationships#dynamic-relationships
     */
    public function bootDynamicRelationships(): void
    {
        if ($this instanceof ProviderDynamicRelationshipsInterface && $this->isDynamicRelationshipsEnabled()) {
            $this->registerDynamicRelationships();
        }
    }

    /**
     * @return bool
     */
    public function isDynamicRelationshipsEnabled(): bool
    {
        return true;
    }
}
