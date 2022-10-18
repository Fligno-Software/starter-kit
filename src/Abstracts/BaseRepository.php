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
 *
 * @since  2021-11-19
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @param  Builder|null  $builder
     */
    public function __construct(protected Builder|null $builder = null)
    {
    }

    /**
     * @return Builder|null
     */
    public function getBuilder(): Builder|null
    {
        return $this->builder?->clone();
    }

    /**
     * @return Builder|null
     */
    public function builder(): Builder|null
    {
        return $this->builder;
    }

    /**
     * @param  mixed  $attributes
     * @return Collection|array|null
     */
    public function all(mixed $attributes = null): Collection|array|null
    {
        return $this->getBuilder()?->get();
    }

    /**
     * @param  mixed  $attributes
     * @return Model|null
     */
    public function make(mixed $attributes = null): Model|null
    {
        return $this->getBuilder()?->firstOrNew($attributes ?? []);
    }

    /**
     * @param  mixed  $attributes
     * @return Model|null
     */
    public function create(mixed $attributes = null): Model|null
    {
        $found_or_new = $this->make($attributes);

        if ($found_or_new && ! $found_or_new->exists) {
            if ($found_or_new->save()) {
                return $found_or_new;
            }

            return null;
        }

        return $found_or_new;
    }

    /**
     * @param  int|string|array|null  $id
     * @param  mixed  $attributes
     * @return Model|Collection|array|null
     */
    public function get(int|string|array $id = null, mixed $attributes = null): Model|Collection|array|null
    {
        if ($id) {
            return $this->getBuilder()?->findOrFail($id);
        }

        return $this->all();
    }

    /**
     * @param  int|string|array|null  $id
     * @param  mixed  $attributes
     * @return Model|Collection|array|null
     */
    public function update(int|string|array $id = null, mixed $attributes = []): Model|Collection|array|null
    {
        $model = $this->get($id);

        if ($model instanceof Model) {
            $model->fill($attributes);
            $model->save();
        } elseif ($model instanceof Collection) {
            $model->toQuery()->update($attributes);
            $model = $model->fresh();
        }

        return $model;
    }

    /**
     * @param  int|string|array|null  $id
     * @param  mixed  $attributes
     * @return Model|Collection|array|null
     */
    public function delete(int|string|array $id = null, mixed $attributes = null): Model|Collection|array|null
    {
        $builder = $this->getBuilder()
            ->when($id, function (Builder $builder) use ($id) {
                $key = $builder->getModel()->getQualifiedKeyName();

                return is_array($id) ? $builder->whereIn($key, $id) : $builder->where($key, $id);
            });

        if ($builder->delete()) {
            $builder = $builder->onlyTrashed();

            return $id && ! is_array($id) ? $builder->first() : $builder->get();
        }

        return null;
    }

    /**
     * @param  int|string|array|null  $id
     * @param  mixed  $attributes
     * @return Model|Collection|array|null
     */
    public function restore(int|string|array $id = null, mixed $attributes = null): Model|Collection|array|null
    {
        $builder = $this->getBuilder();

        if ($builder?->hasMacro('restore')) {
            $builder = $builder
                ->when($id, function (Builder $builder) use ($id) {
                    $key = $builder->getModel()->getQualifiedKeyName();

                    return is_array($id) ? $builder->whereIn($key, $id) : $builder->where($key, $id);
                });

            if ($builder->clone()->onlyTrashed()->restore()) {
                return $id && ! is_array($id) ? $builder->first() : $builder->get();
            }
        }

        return null;
    }
}
