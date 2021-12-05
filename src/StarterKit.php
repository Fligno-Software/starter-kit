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
     * @param string $repositoriesPath
     * @param string|null $modelsPath
     */
    public function registerRepositories(string $repositoriesPath, string $modelsPath = null): void
    {
        if (! $this->verifyPathsExist($repositoriesPath, $modelsPath)) {
            return;
        }

        $repositoriesClasses = $this->getClassesFromPath($repositoriesPath, 'Repository');
        $modelsClasses = $this->getClassesFromPath($modelsPath);

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
     * @param string $policiesPath
     * @param string|null $modelsPath
     */
    public function registerPolicies(string $policiesPath, string $modelsPath = null): void
    {
        if (! $this->verifyPathsExist($policiesPath, $modelsPath)) {
            return;
        }

        $policiesClasses = $this->getClassesFromPath($policiesPath, 'Policy');
        $modelsClasses = $this->getClassesFromPath($modelsPath);

        $policiesClasses->each(static function ($policy, $key) use ($modelsClasses) {
            $model = $modelsClasses->get($key);
            if ($model) {
                Gate::policy($model, $policy);
            }
        });
    }

    /**
     * @param string $observersPath
     * @param string|null $modelsPath
     */
    public function registerObservers(string $observersPath, string $modelsPath = null): void
    {
        if (! $this->verifyPathsExist($observersPath, $modelsPath)) {
            return;
        }

        $observersClasses = $this->getClassesFromPath($observersPath, 'Observer');
        $modelsClasses = $this->getClassesFromPath($modelsPath);

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

    /**
     * @param string $path
     * @param string|null $suffix
     * @return Collection
     */
    private function getClassesFromPath(string$path, string $suffix = null): Collection
    {
        $classPaths = array_keys(ClassMapGenerator::createMap($path));

        $classes = [];

        foreach ($classPaths as $classPath) {
            $key = (string) Str::of($classPath)->afterLast('\\')->before($suffix ?? '');
            if ($key) {
                $classes[$key] = $classPath;
            }
        }

        return collect($classes);
    }
}
