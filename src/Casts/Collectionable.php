<?php

namespace Fligno\StarterKit\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use JsonException;

/**
 * Class Collectionable
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
class Collectionable implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return Collection|string|null
     * @throws JsonException
     */
    public function get($model, string $key, $value, array $attributes): Collection|string|null
    {
        return collection_decode($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return string|Collection|null
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes): string|Collection|null
    {
        return collection_encode($value);
    }
}
