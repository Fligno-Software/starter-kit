<?php

namespace Fligno\StarterKit\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Trait UsesProviderRepositoryMapTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesProviderRepositoryMapTrait
{
    /**
     * Laravel Repository Map
     * @example [ UserRepository::class => User::class ]
     *
     * @var array
     */
    protected array $repository_map = [];

    /**
     * @return bool
     */
    public function areRepositoriesEnabled(): bool
    {
        return config('starter-kit.repositories_enabled');
    }

    /**
     * Load Repositories
     *
     * @param Collection|null $repositories
     * @return void
     */
    protected function loadRepositories(Collection $repositories = null): void
    {
        $repositories?->each(static function ($model, $repository) {
            if ($model instanceof Collection) {
                $model = $model->first();
            }
            if ($model && class_exists($model) && class_exists($repository)) {
                app()->when($repository)->needs(Builder::class)->give(fn() => call_user_func($model . '::query'));
            }
        });
    }
}
