<?php

namespace Fligno\StarterKit;

use Composer\Autoload\ClassMapGenerator;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

class StarterKit
{
    /**
     * @param string $repositoriesPath
     * @param string|null $modelsPath
     */
    public function registerRepositories(string $repositoriesPath, string $modelsPath = null): void
    {
        // Check if both paths exist
        if (file_exists($repositoriesPath)) {
            if (! file_exists($modelsPath) && file_exists($tempPath = $repositoriesPath . '/../Models')) {
                $modelsPath = $tempPath;
            }
        }
        else {
            return;
        }

        // Get Real Path instead of Relative Path
        if ($repositoriesPath && $modelsPath) {
            $repositoriesPath = realpath($repositoriesPath);
            $modelsPath = realpath($modelsPath);
        }

        $repositoriesClasses = $this->getClassesFromPath($repositoriesPath);
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
     * @param $path
     * @return Collection
     */
    private function getClassesFromPath($path): Collection
    {
        $classPaths = array_keys(ClassMapGenerator::createMap($path));

        $classes = [];

        foreach ($classPaths as $classPath) {
            $key = (string) Str::of($classPath)->afterLast('\\')->before('Repository');
            if ($key) {
                $classes[$key] = $classPath;
            }
        }

        return collect($classes);
    }

    /**
     * @param $classString
     * @return mixed
     * @throws ReflectionException
     */
    protected function getModelNamespaceFromClass($classString): mixed
    {
        $class = new ReflectionClass($classString);
        try {
            $namespace = $class->getStaticPropertyValue('modelNamespace');
        } catch (Exception $e) {
            return null;
        }

        return $namespace;
    }
}
