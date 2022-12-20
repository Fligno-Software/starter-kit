<?php

namespace Fligno\StarterKit\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait ModelOwnedTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait ModelOwnedTrait
{
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
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(starterKit()->getUserModel(), self::getOwnerIdColumn());
    }
}
