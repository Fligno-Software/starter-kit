<?php

namespace Fligno\StarterKit\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class BaseDataFactory
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since 2022-05-04
 */
abstract class BaseDataFactory extends BaseJsonSerializable
{
    /**
     * @return Builder
     * @example User::query()
     */
    abstract public function getBuilder(): Builder;

    /**
     * @param BaseJsonSerializable|Response|Request|Collection|Model|array|null $data
     * @param string|null $key
     * @return Builder|Model
     */
    public function make(
        BaseJsonSerializable|Response|Request|Collection|Model|array|null $data = [],
        ?string $key = null
    ): Model|Builder {
        $this->mergeDataToFields($data, $key);

        $model = $this->getBuilder()->getModel()->newModelInstance();

        $this->collect()->each(fn($item, $key) => $model->$key = $item);

        return $model;
    }

    /**
     * @param BaseJsonSerializable|Response|Request|Collection|Model|array|null $data
     * @param string|null $key
     * @return Model|Builder|null
     */
    public function create(
        BaseJsonSerializable|Response|Request|Collection|Model|array|null $data = [],
        ?string $key = null
    ): Model|Builder|null {
        $model = $this->make($data, $key);

        return $model->save() ? $model : null;
    }
}
