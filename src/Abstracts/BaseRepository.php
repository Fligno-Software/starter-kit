<?php

namespace Fligno\StarterKit\Abstracts;

use Fligno\StarterKit\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Abstract Class BaseRepository
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since 2021-11-19
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @param Builder $builder
     */
    public function __construct(protected Builder $builder)
    {
        //
    }

    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        return $this->builder;
    }

    /**
     * @return Builder[]|Collection
     */
    public function all(): Collection|array
    {
        return $this->builder->get();
    }

    /**
     * @param $attributes
     * @return Builder|Model
     */
    public function create($attributes): Model|Builder
    {
        return $this->builder->firstOrCreate($attributes);
    }

    /**
     * @param $id
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function get($id = null): Model|Collection|Builder|array|null
    {
        if ($id) {
            return $this->builder->findOrFail($id);
        }

        return $this->all();
    }

    /**
     * @param $id
     * @param $attributes
     * @return Model|Collection|Builder|array|null
     */
    public function update($id, $attributes): Model|Collection|Builder|array|null
    {
        $model = $this->get($id);
        $model->fill($attributes);
        $this->setRelationships($model, $attributes);
        $model->save();

        return $model;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id): mixed
    {
        return $this->get($id)?->delete();
    }

    /**
     * @param $id
     */
    public function restore($id): void
    {
        //Todo: check if a model uses SoftDeletes before using the restore command
    }

    /**
     * @param $model
     * @param array $attributes
     */
    protected function setRelationships($model, array $attributes): void
    {
        if (method_exists($model, 'setRelationships')) {
            $model->setRelationships($attributes);
        }
    }
}
