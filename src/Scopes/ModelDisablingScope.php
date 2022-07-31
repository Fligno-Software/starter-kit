<?php

namespace Fligno\StarterKit\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class ModelDisablingScope
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
class ModelDisablingScope implements Scope
{
    /**
     * All the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected array $extensions = ['Enable', 'Disable', 'WithDisabled', 'WithoutDisabled', 'OnlyDisabled'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder  $builder
     * @param  Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNull($model->getQualifiedDisabledAtColumn());
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
     * Get the "disabled at" column for the builder.
     *
     * @param  Builder  $builder
     * @return string
     */
    protected function getDisabledAtColumn(Builder $builder): string
    {
        if (count((array) $builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedDisabledAtColumn();
        }

        return $builder->getModel()->getDisabledAtColumn();
    }

    /**
     * Add the enable extension to the builder.
     *
     * @param  Builder  $builder
     * @return void
     */
    protected function addEnable(Builder $builder): void
    {
        $builder->macro('enable', function (Builder $builder) {
            $builder->withDisabled();

            return $builder->update([$builder->getModel()->getDisabledAtColumn() => null]);
        });
    }

    /**
     * Add to disable extension to the builder.
     *
     * @param  Builder  $builder
     * @return void
     */
    protected function addDisable(Builder $builder): void
    {
        $builder->macro('disable', function (Builder $builder) {
            $column = $this->getDisabledAtColumn($builder);

            return $builder->update([
                $column => $builder->getModel()->freshTimestampString(),
            ]);
        });
    }

    /**
     * Add the with-disabled extension to the builder.
     *
     * @param  Builder  $builder
     * @return void
     */
    protected function addWithDisabled(Builder $builder): void
    {
        $builder->macro('withDisabled', function (Builder $builder, $withDisabled = true) {
            if (! $withDisabled) {
                return $builder->withoutDisabled();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-disabled extension to the builder.
     *
     * @param  Builder  $builder
     * @return void
     */
    protected function addWithoutDisabled(Builder $builder): void
    {
        $builder->macro('withoutDisabled', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->whereNull(
                $model->getQualifiedDisabledAtColumn()
            );

            return $builder;
        });
    }

    /**
     * Add the only-disabled extension to the builder.
     *
     * @param  Builder  $builder
     * @return void
     */
    protected function addOnlyDisabled(Builder $builder): void
    {
        $builder->macro('onlyDisabled', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->whereNotNull(
                $model->getQualifiedDisabledAtColumn()
            );

            return $builder;
        });
    }
}
