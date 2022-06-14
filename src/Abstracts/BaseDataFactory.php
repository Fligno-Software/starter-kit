<?php

namespace Fligno\StarterKit\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use ReflectionException;

/**
 * Class BaseDataFactory
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since 2022-05-04
 */
abstract class BaseDataFactory extends BaseJsonSerializable
{
    /**
     * @return Builder
     * @example User::query()
     */
    abstract public function getBuilder(): Builder;

    /**
     * @param mixed $data
     * @param string|null $key
     * @return Builder|Model
     * @throws ReflectionException
     */
    public function make(mixed $data = [], ?string $key = null): Model|Builder
    {
        $this->mergeDataToFields($data, $key);

        $model = $this->getBuilder()->getModel()->newModelInstance();

        $this->collect()->each(function ($item, $key) use ($model) {
            $model->$key = $item;
        });

        return $model;
    }

    /**
     * @param mixed $data
     * @param string|null $key
     * @return Model|Builder|null
     * @throws ReflectionException
     */
    public function create(mixed $data = [], ?string $key = null): Model|Builder|null
    {
        $model = $this->make($data, $key);

        return $model->save() ? $model : null;
    }

    /**
     * @param mixed $attributes
     * @param mixed $values
     * @param string|null $attributes_key
     * @param string|null $values_key
     * @return Model|Builder|null
     * @throws ReflectionException
     */
    public function firstOrCreate(mixed $attributes = [], ?string $attributes_key = null, mixed $values = [], ?string $values_key = null): Model|Builder|null
    {
        $data = $this->parse($attributes, $attributes_key);

        if (Arr::isAssoc($data)) {
            $data = collect($data)->only($this->collect()->keys());
        }
        else {
            $data = $this->collect()->only($data);

            if (! $data->count()) {
                $data = $this->collect();
            }
        }

        $builder = $this->getBuilder();

        $data->each(function ($item, $key) use ($builder) {
            $builder->where($key, $item);
        });

        if ($model = $builder->first()) {
            return $model;
        }

        $values = collect($this->parse($values, $values_key));

        $model = $this->make($values);

        return $model->save() ? $model : null;
    }
}
