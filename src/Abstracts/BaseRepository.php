<?php

namespace Fligno\StarterKit\Abstracts;

use App\Models\User;
use Fligno\StarterKit\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;

/**
 * Abstract Class BaseRepository
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 *
 * @since  2021-11-19
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @param  Builder  $builder
     */
    public function __construct(protected Builder $builder)
    {
        //
    }

    /**
     * @return Builder
     */
    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        return $this->builder;
    }

    /**
     * @param  BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array|null  $attributes
     * @param  User|null  $user
     * @return Collection|array
     */
    public function all(
        Model|array|Response|\Illuminate\Support\Collection|Request|BaseJsonSerializable $attributes = null,
        User $user = null
    ): Collection|array {
        return $this->builder->get();
    }

    /**
     * @param  BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array  $attributes
     * @param  User|null  $user
     * @return Model|null
     */
    public function create(Model|array|Response|\Illuminate\Support\Collection|Request|BaseJsonSerializable $attributes, User $user = null): Model|null
    {
        return $this->builder->firstOrCreate($attributes);
    }

    /**
     * @param  int|string  $id
     * @param  BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array|null  $attributes
     * @param  User|null  $user
     * @return Model|Collection|Builder|array|null
     */
    public function get(
        int|string $id,
        Model|array|Response|\Illuminate\Support\Collection|Request|BaseJsonSerializable $attributes = null,
        User $user = null
    ): Model|Collection|Builder|array|null {
        if ($id) {
            return $this->builder->findOrFail($id);
        }

        return $this->all();
    }

    /**
     * @param  int|string  $id
     * @param  BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array|null  $attributes
     * @param  User|null  $user
     * @return Model|null
     */
    public function update(
        int|string $id,
        Model|array|Response|\Illuminate\Support\Collection|Request|BaseJsonSerializable $attributes = null,
        User $user = null
    ): Model|null {
        $model = $this->get($id);
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * @param  int|string  $id
     * @param  BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array|null  $attributes
     * @param  User|null  $user
     * @return Model|null
     */
    public function delete(
        int|string $id,
        Model|array|Response|\Illuminate\Support\Collection|Request|BaseJsonSerializable $attributes = null,
        User $user = null
    ): Model|null {
        return $this->get($id)?->delete();
    }

    /**
     * @param  int|string  $id
     * @param  BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array|null  $attributes
     * @param  User|null  $user
     * @return Model|null
     */
    public function restore(
        int|string $id,
        Model|array|Response|\Illuminate\Support\Collection|Request|BaseJsonSerializable $attributes = null,
        User $user = null
    ): Model|null {
        //Todo: check if a model uses SoftDeletes before using the restore command

        return null;
    }
}
