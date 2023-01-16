<?php

namespace Fligno\StarterKit\Traits;

use Fligno\StarterKit\Scopes\ModelDisablingScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait ModelDisablesTrait
 *
 * @method static static|int disable()
 * @method static static|int enable()
 * @method static static|Builder|\Illuminate\Database\Query\Builder withDisabled(bool $with_disabled = true)
 * @method static static|Builder|\Illuminate\Database\Query\Builder onlyDisabled()
 * @method static static|Builder|\Illuminate\Database\Query\Builder withoutDisabled()
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait ModelDisablingTrait
{
    /**
     * Boot the disabling trait for a model.
     *
     * @return void
     */
    public static function bootModelDisablingTrait(): void
    {
        static::addGlobalScope(new ModelDisablingScope());
    }

    /**
     * Initialize the disabling trait for an instance.
     *
     * @return void
     */
    public function initializeModelDisablingTrait(): void
    {
        if (! isset($this->casts[$this->getDisabledAtColumn()])) {
            $this->casts[$this->getDisabledAtColumn()] = 'datetime';
        }
    }

    /**
     * Get the name of the "disabled at" column.
     *
     * @return string
     */
    public static function getDisabledAtColumn(): string
    {
        return defined('static::DISABLED_AT') ? static::DISABLED_AT : 'disabled_at';
    }

    /**
     * Get the fully qualified "disabled at" column.
     *
     * @return string
     */
    public function getQualifiedDisabledAtColumn(): string
    {
        return $this->qualifyColumn($this->getDisabledAtColumn());
    }

    /**
     * @return bool
     */
    public function getIsEnabledAttribute(): bool
    {
        $column = $this->getDisabledAtColumn();

        return is_null($this->$column);
    }
}
