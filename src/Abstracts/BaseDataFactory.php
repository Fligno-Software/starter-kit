<?php

namespace Fligno\StarterKit\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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
     * To avoid duplicate entries on database, checking if the model already exists by its unique keys is a must.
     *
     * @return array
     */
    public function getUniqueKeys(): array
    {
        return [];
    }

    /**
     * @param mixed $data
     * @param string|null $key
     * @return Builder|Model
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
     */
    public function firstOrNew(mixed $attributes = [], ?string $attributes_key = null, mixed $values = [], ?string $values_key = null): Model|Builder|null
    {
        // Normalize attributes to array
        $attributes = $this->parse($attributes, $attributes_key);

        // Get copy of field keys
        $field_keys = $this->collectClassVars()->keys();

        // If attributes is non-empty and associative, remove unnecessary keys
        // If attributes is non-empty but not associative, it means it's just an array of keys

        if (Arr::isAssoc($attributes)) {
            $attributes = collect($attributes)->only($field_keys);
        } else {
            // The attributes becomes keys
            $keys = collect($attributes);

            // Intersect keys with field keys then get unique values
            $keys = $keys->intersect($field_keys)->unique();

            // The attributes now comes from current object's field values
            $attributes = $this->collect()->only($keys);
        }

        // If attributes is still empty, try using unique keys
        if ($attributes->isEmpty()) {
            $keys = collect($this->getUniqueKeys())->intersect($field_keys)->unique();
            $attributes = $this->collect()->only($keys);
        }

        // If attributes is not empty, check if exists on database
        if ($attributes->isNotEmpty()) {
            $builder = $this->getBuilder();

            $attributes->each(function ($item, $key) use ($builder) {
                $builder->where($key, $item);
            });

            if ($model = $builder->first()) {
                return $model;
            }
        }

        // Merge values to attributes
        if (($values = $this->parse($values, $values_key)) && Arr::isAssoc($values)) {
            $attributes = $attributes->merge($values)->only($field_keys);
        }

        return $this->make($attributes);
    }

    /**
     * @param mixed $attributes
     * @param mixed $values
     * @param string|null $attributes_key
     * @param string|null $values_key
     * @return Model|Builder|null
     */
    public function firstOrCreate(mixed $attributes = [], ?string $attributes_key = null, mixed $values = [], ?string $values_key = null): Model|Builder|null
    {
        $model = $this->firstOrNew($attributes, $attributes_key, $values, $values_key);

        return $model->save() ? $model : null;
    }
}
