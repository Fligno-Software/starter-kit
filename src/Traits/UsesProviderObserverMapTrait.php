<?php

namespace Fligno\StarterKit\Traits;

use Exception;
use Illuminate\Support\Collection;

/**
 * Trait UsesProviderObserverMapTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesProviderObserverMapTrait
{
    /**
     * Laravel Observer Map
     *
     * @link    https://laravel.com/docs/8.x/eloquent#observers
     *
     * @example [ UserObserver::class => User::class ]
     *
     * @var array
     */
    protected array $observer_map = [];

    /**
     * @return bool
     */
    public function areObserversEnabled(): bool
    {
        return config('starter-kit.observers_enabled');
    }

    /**
     * Load Observers
     *
     * @param  Collection|null  $observers
     * @return void
     */
    protected function loadObservers(Collection $observers = null): void
    {
        $observers?->each(
            static function ($model, $observer) {
                if ($model instanceof Collection) {
                    $model = $model->first();
                }
                try {
                    call_user_func($model.'::observe', $observer);
                } catch (Exception) {
                    starterKit()->clearCache();
                }
            }
        );
    }
}
