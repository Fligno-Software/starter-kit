<?php

namespace Fligno\StarterKit\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class ModelExpiringScope
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
class ModelExpiringScope implements Scope
{
    /**
     * All the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected array $extensions = ['Unexpire', 'Expire', 'WithExpired', 'WithoutExpired', 'OnlyExpired'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder  $builder
     * @param  Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNull($model->getQualifiedExpiresAtColumn());
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  Builder  $builder
     * @return void
     */
    public function extend(Builder $builder): void
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Get the "expires at" column for the builder.
     *
     * @param  Builder  $builder
     * @return string
     */
    protected function getExpiresAtColumn(Builder $builder): string
    {
        if (count($builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedExpiresAtColumn();
        }

        return $builder->getModel()->getExpiresAtColumn();
    }

    /**
     * Add the unexpire extension to the builder.
     *
     * @param  Builder  $builder
     * @return void
     */
    protected function addUnexpire(Builder $builder): void
    {
        $builder->macro('unexpire', function (Builder $builder) {
            $builder->withExpired();

            return $builder->update([$builder->getModel()->getExpiresAtColumn() => null]);
        });
    }

    /**
     * Add to expire extension to the builder.
     *
     * @param  Builder  $builder
     * @return void
     */
    protected function addExpire(Builder $builder): void
    {
        $builder->macro('expire', function (Builder $builder) {
            $column = $this->getExpiresAtColumn($builder);

            return $builder->update([
                $column => $builder->getModel()->freshTimestampString(),
            ]);
        });
    }

    /**
     * Add the with-expired extension to the builder.
     *
     * @param  Builder  $builder
     * @return void
     */
    protected function addWithExpired(Builder $builder): void
    {
        $builder->macro('withExpired', function (Builder $builder, $withExpired = true) {
            if (! $withExpired) {
                return $builder->withoutExpired();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-expired extension to the builder.
     *
     * @param  Builder  $builder
     * @return void
     */
    protected function addWithoutExpired(Builder $builder): void
    {
        $builder->macro('withoutExpired', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->whereNull(
                $model->getQualifiedExpiresAtColumn()
            );

            return $builder;
        });
    }

    /**
     * Add the only-expired extension to the builder.
     *
     * @param  Builder  $builder
     * @return void
     */
    protected function addOnlyExpired(Builder $builder): void
    {
        $builder->macro('onlyExpired', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->whereNotNull(
                $model->getQualifiedExpiresAtColumn()
            );

            return $builder;
        });
    }
}
