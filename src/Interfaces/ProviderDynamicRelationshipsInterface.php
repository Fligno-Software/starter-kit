<?php

namespace Fligno\StarterKit\Interfaces;

/**
 * Interface ProviderDynamicRelationshipsInterface
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
interface ProviderDynamicRelationshipsInterface
{
    /**
     * @return void
     * @link https://laravel.com/docs/8.x/eloquent-relationships#dynamic-relationships
     */
    public function registerDynamicRelationships(): void;
}
