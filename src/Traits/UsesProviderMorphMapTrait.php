<?php

namespace Fligno\StarterKit\Traits;

/**
 * Trait UsesProviderMorphMapTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesProviderMorphMapTrait
{
    /**
     * Polymorphism Morph Maps
     *
     * @var array
     */
    protected array $morph_map = [];

    /**
     * @return bool
     */
    public function isMorphMapEnabled(): bool
    {
        return config('starter-kit.enforce_morph_map');
    }

    /**
     * @return void
     */
    public function bootMorphMap(): void
    {
        if ($this->isMorphMapEnabled()) {
            enforceMorphMap($this->morph_map);
        }
    }
}
