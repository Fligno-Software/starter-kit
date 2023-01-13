<?php

namespace Fligno\StarterKit\Traits;

use Fligno\StarterKit\Scopes\ModelOwnedScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait ModelOwnedTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait ModelOwnedTrait
{
    /**
     * Boot the owned trait for a model.
     *
     * @return void
     */
    public static function bootModelOwnedTrait(): void
    {
        static::addGlobalScope(new ModelOwnedScope());
    }

    /**
     * Get the name of the "owner id" column.
     *
     * @return string
     */
    public static function getOwnerIdColumn(): string
    {
        return defined('static::OWNER_ID') ? static::OWNER_ID : 'owner_id';
    }

    /**
     * Get the fully qualified "owner id" column.
     *
     * @return string
     */
    public function getQualifiedOwnerIdColumn(): string
    {
        return $this->qualifyColumn($this->getOwnerIdColumn());
    }

    /***** RELATIONSHIPS *****/

    /**
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(starterKit()->getUserModel(), self::getOwnerIdColumn());
    }
}
