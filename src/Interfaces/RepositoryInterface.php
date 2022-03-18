<?php

namespace Fligno\StarterKit\Interfaces;

use App\Models\User;
use Fligno\StarterKit\Abstracts\BaseJsonSerializable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;

/**
 * Interface RepositoryInterface
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since 2021-11-19
 */
interface RepositoryInterface
{
    /**
     * @param BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array|null $attributes
     * @param User|null $user
     * @return Collection|array
     */
    public function all(BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array $attributes = null, User $user = null): Collection|array;

    /**
     * @param int|string $id
     * @param BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array|null $attributes
     * @param User|null $user
     * @return Model|Collection|Builder|array|null
     */
    public function get(int|string $id, BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array $attributes = null, User $user = null): Model|Collection|Builder|array|null;

    /**
     * @param BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array $attributes
     * @param User|null $user
     * @return Model|null
     */
    public function create(BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array $attributes, User $user = null): Model|null;

    /**
     * @param int|string $id
     * @param BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array|null $attributes
     * @param User|null $user
     * @return Model|null
     */
    public function update(int|string $id, BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array $attributes = null, User $user = null): Model|null;

    /**
     * @param int|string $id
     * @param BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array|null $attributes
     * @param User|null $user
     * @return Model|null
     */
    public function delete(int|string $id, BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array $attributes = null, User $user = null): Model|null;

    /**
     * @param int|string $id
     * @param BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array|null $attributes
     * @param User|null $user
     * @return Model|null
     */
    public function restore(int|string $id, BaseJsonSerializable|Response|Request|\Illuminate\Support\Collection|Model|array $attributes = null, User $user = null): Model|null;
}
