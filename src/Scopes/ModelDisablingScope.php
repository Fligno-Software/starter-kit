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
        $builder->withoutDisabled();
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
        if (count($builder->getQuery()->joins ?? []) > 0) {
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

            return $builder->update([$this->getDisabledAtColumn($builder) => null]);
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

            return $builder->update([$column => $builder->getModel()->freshTimestampString()]);
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
        $builder->macro('withDisabled', function (Builder $builder, $with_disabled = true) {
            if (! $with_disabled) {
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
            $builder->withoutGlobalScope($this)->whereNull($this->getDisabledAtColumn($builder));

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
            $builder->withoutGlobalScope($this)->whereNotNull($this->getDisabledAtColumn($builder));

            return $builder;
        });
    }
}
