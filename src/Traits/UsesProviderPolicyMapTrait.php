<?php

namespace Fligno\StarterKit\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/**
 * Trait UsesProviderPolicyMapTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesProviderPolicyMapTrait
{
    /**
     * Laravel Policy Map
     * @link https://laravel.com/docs/8.x/authorization#registering-policies
     * @example [ UserPolicy::class => User::class ]
     *
     * @var array
     */
    protected array $policy_map = [];

    /**
     * @return bool
     */
    public function arePoliciesEnabled(): bool
    {
        return config('starter-kit.policies_enabled');
    }

    /**
     * Load Policies
     *
     * @param Collection|null $policies
     * @return void
     */
    protected function loadPolicies(Collection $policies = null): void
    {
        $policies?->each(static function ($model, $policy) {
            if ($model instanceof Collection) {
                $model = $model->first();
            }
            if ($model) {
                Gate::policy($model, $policy);
            }
        });
    }
}
