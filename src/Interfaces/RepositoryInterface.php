<?php

namespace Fligno\StarterKit\Interfaces;

/**
 * Interface RepositoryInterface
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since 2021-11-19
 */
interface RepositoryInterface
{
    public function all();
    public function get($id);
    public function create($attributes);
    public function update($id, $attributes);
    public function delete($id);
    public function restore($id);
}
