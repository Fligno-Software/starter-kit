<?php

namespace Fligno\StarterKit\Traits;

use Fligno\StarterKit\Scopes\ModelExpiringScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait ModelExpiringTrait
 *
 * @method static static|Builder|\Illuminate\Database\Query\Builder withExpired(bool $withExpired = true)
 * @method static static|Builder|\Illuminate\Database\Query\Builder onlyExpired(bool $onlyExpired = true)
 * @method static static|Builder|\Illuminate\Database\Query\Builder withoutExpired(bool $withoutExpired = true)
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
        return is_null($this->getExpiresAtColumn());
    }
}
