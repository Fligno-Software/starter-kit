<?php

namespace Fligno\StarterKit;

use Composer\Autoload\ClassMapGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * Class StarterKit
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since 2021-11-25
 */
class StarterKit
{
    /**
     * @param string|null $repositoriesPath
     * @param string|null $modelsPath
     */
    public function registerRepositories(string $repositoriesPath = null, string $modelsPath = null): void
    {
        if (empty($repositoriesPath)) {
            return;
        }

        if (! $this->verifyPathsExist($repositoriesPath, $modelsPath)) {
            return;
        }

        $repositoriesClasses = collectClassesFromPath($repositoriesPath, 'Repository');
        $modelsClasses = collectClassesFromPath($modelsPath);

        $repositoriesClasses->each(static function ($repo, $key) use ($modelsClasses) {
            $model = $modelsClasses->get($key);
            if ($model) {
                app()->when($repo)->needs(Builder::class)->give(function () use ($model) {
                    return call_user_func($model . '::query');
                });
            }
        });
    }

    /**
     * @param string|null $policiesPath
     * @param string|null $modelsPath
     */
    public function registerPolicies(string $policiesPath = null, string $modelsPath = null): void
    {
        if (empty($policiesPath)) {
            return;
        }

        if (! $this->verifyPathsExist($policiesPath, $modelsPath)) {
            return;
        }

        $policiesClasses = collectClassesFromPath($policiesPath, 'Policy');
        $modelsClasses = collectClassesFromPath($modelsPath);

        $policiesClasses->each(static function ($policy, $key) use ($modelsClasses) {
            $model = $modelsClasses->get($key);
            if ($model) {
                Gate::policy($model, $policy);
            }
        });
    }

    /**
     * @param string|null $observersPath
     * @param string|null $modelsPath
     */
    public function registerObservers(string $observersPath = null, string $modelsPath = null): void
    {
        if (empty($observersPath)) {
            return;
        }

        if (! $this->verifyPathsExist($observersPath, $modelsPath)) {
            return;
        }

        $observersClasses = collectClassesFromPath($observersPath, 'Observer');
        $modelsClasses = collectClassesFromPath($modelsPath);

        $observersClasses->each(static function ($observer, $key) use ($modelsClasses) {
            $model = $modelsClasses->get($key);
            if ($model) {
                call_user_func($model . '::observe', $observer);
            }
        });
    }

    /**
     * @param string $folderPath
     * @param string|null $modelsPath
     * @return bool
     */
    private function verifyPathsExist(string &$folderPath, string &$modelsPath = null): bool
    {
        $modelsPath = null;
        // Check if both paths exist
        if (file_exists($folderPath)) {
            if (! file_exists($modelsPath) && file_exists($tempPath = $folderPath . '/../Models')) {
                $modelsPath = $tempPath;
            }

            // Convert Relative Paths to Real Paths
            $folderPath = realpath($folderPath);
            $modelsPath = realpath($modelsPath);

            return true;
        }

        return false;
    }
}
