<?php

namespace Fligno\StarterKit\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Trait UsesUUIDTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since 2021-11-19
 */
trait UsesUUIDTrait
{
    /**
     * Generates a UUID during model creation.
     */
    public static function bootUsesUuid(): void
    {
        static::creating(function (Model $model) {
            $model->uuid = Str::uuid();
        });
    }
}
