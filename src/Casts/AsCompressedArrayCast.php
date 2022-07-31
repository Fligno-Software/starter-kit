<?php

namespace Fligno\StarterKit\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AsCompressedArrayCast
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 *
 * @since 2022-05-04
 */
class AsCompressedArrayCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array|null
     */
    public function get($model, string $key, $value, array $attributes): array|null
    {
        return $value ? json_decode(gzinflate($value), true) : null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string|bool|null
     */
    public function set($model, string $key, $value, array $attributes): string|bool|null
    {
        return is_array($value) ? gzdeflate(json_encode($value), 9) : null;
    }
}
