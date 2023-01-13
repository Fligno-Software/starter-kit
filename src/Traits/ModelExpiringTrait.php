<?php

namespace Fligno\StarterKit\Traits;

use Fligno\StarterKit\Scopes\ModelExpiringScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Trait ModelExpiringTrait
 *
 * @method static static|int expire(Carbon|string|null $date_time = null)
 * @method static static|int unexpire()
 * @method static static|Builder|\Illuminate\Database\Query\Builder withExpired(bool $with_expired = true, Carbon|string|null $date_time = null)
 * @method static static|Builder|\Illuminate\Database\Query\Builder onlyExpired(Carbon|string|null $date_time = null)
 * @method static static|Builder|\Illuminate\Database\Query\Builder withoutExpired(Carbon|string|null $date_time = null)
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait ModelExpiringTrait
{
    /**
     * Boot the expiring trait for a model.
     *
     * @return void
     */
    public static function bootModelExpiringTrait(): void
    {
        static::addGlobalScope(new ModelExpiringScope());
    }

    /**
     * Initialize the expiring trait for an instance.
     *
     * @return void
     */
    public function initializeModelExpiringTrait(): void
    {
        if (! isset($this->casts[$this->getExpiresAtColumn()])) {
            $this->casts[$this->getExpiresAtColumn()] = 'datetime';
        }
    }

    /**
     * Get the name of the "expires at" column.
     *
     * @return string
     */
    public function getExpiresAtColumn(): string
    {
        return defined('static::EXPIRES_AT') ? static::EXPIRES_AT : 'expires_at';
    }

    /**
     * Get the fully qualified "expires at" column.
     *
     * @return string
     */
    public function getQualifiedExpiresAtColumn(): string
    {
        return $this->qualifyColumn($this->getExpiresAtColumn());
    }

    /**
     * @return bool
     */
    public function getIsExpiredAttribute(): bool
    {
        $column = $this->getExpiresAtColumn();
        $value = $this->$column;

        return $value && $value->isPast();
    }
}