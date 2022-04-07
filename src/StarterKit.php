<?php

namespace Fligno\StarterKit;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Class StarterKit
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since 2021-11-25
 */
class StarterKit
{
    protected string $main_tag = 'sk';

    // Setters & Getters

    /**
     * @param string $main_tag
     */
    public function setMainTag(string $main_tag): void
    {
        $this->main_tag = $main_tag;
    }

    /**
     * @return string
     */
    public function getMainTag(): string
    {
        return $this->main_tag;
    }

    // Methods

    /**
     * @return bool
     */
    public function isCacheTaggable(): bool
    {
        return method_exists(Cache::getStore(), 'tags');
    }

    /**
     * @return bool
     */
    public function clearCache(): bool
    {
        if ($this->isCacheTaggable()) {
            return Cache::tags($this->getMainTag())->flush();
        }

        return false;
    }

    /**
     * @param  string|null ...$tags
     * @return array
     */
    public function getTags(string|null ...$tags): array
    {
        return collect($this->getMainTag())->merge($tags)->filter()->toArray();
    }

    /**
     * @param  array   $tags
     * @param  $key
     * @param  Closure $closure
     * @return array|mixed
     */
    private function getCache(array $tags, $key, Closure $closure): mixed
    {
        if ($this->isCacheTaggable()) {
            return Cache::tags($tags)->rememberForever($key, $closure);
        }

        return $closure();
    }

    /**
     * @param  string $package_name
     * @param  string $directory
     * @return Collection|null
     */
    public function getDomains(string $package_name, string $directory): ?Collection
    {
        return $this->getCache(
            $this->getTags($package_name),
            'domains',
            function () use ($directory) {
                $domainPath = guess_file_or_directory_path($directory, 'Domains');
                return collect_files_or_directories($domainPath, true, false, true);
            }
        );
    }

    /**
     * @param  string  $package_name
     * @param  Closure $callable
     * @return Collection|null
     */
    public function getTargetDirectories(string $package_name, Closure $callable): ?Collection
    {
        return $this->getCache(
            $this->getTags($package_name),
            'directories',
            function () use ($callable) {
                return $callable();
            }
        );
    }

    /**
     * @param  string                  $package_name
     * @param  object|string           $sourceObjectOrClassOrDir
     * @param  Collection|array|string $targetFileOrFolder
     * @param  string|null             $domain
     * @param  bool                    $traverseUp
     * @param  int                     $maxLevels
     * @return Collection|array|string|null
     */
    public function getTargetDirectoriesPaths(
        string $package_name,
        object|string $sourceObjectOrClassOrDir,
        Collection|array|string $targetFileOrFolder,
        string $domain = null,
        bool $traverseUp = false,
        int $maxLevels = 3
    ): Collection|array|string|null {
        return $this->getCache(
            $this->getTags($package_name, $domain),
            'paths',
            function () use ($maxLevels, $traverseUp, $targetFileOrFolder, $sourceObjectOrClassOrDir) {
                return guess_file_or_directory_path(
                    $sourceObjectOrClassOrDir,
                    $targetFileOrFolder,
                    $traverseUp,
                    $maxLevels
                );
            }
        );
    }

    /**
     * @param  string      $package_name
     * @param  string      $directory
     * @param  string|null $domain
     * @return Collection|null
     */
    public function getHelpers(string $package_name, string $directory, string $domain = null): ?Collection
    {
        return $this->getCache(
            $this->getTags($package_name, $domain),
            'helpers',
            function () use ($directory) {
                return collect_files_or_directories($directory, false, true, true);
            }
        );
    }

    /**
     * @param  string      $package_name
     * @param  string      $directory
     * @param  string|null $domain
     * @return Collection|null
     */
    public function getRoutes(string $package_name, string $directory, string $domain = null): ?Collection
    {
        return $this->getCache(
            $this->getTags($package_name, $domain),
            'routes',
            function () use ($directory) {
                return collect_files_or_directories($directory, false, true, true);
            }
        );
    }

    /**
     * @return Collection|null
     */
    public function getDefaultPossibleModels(): ?Collection
    {
        return $this->getCache(
            $this->getTags(null),
            'models',
            function () {
                return collect_classes_from_path(app_path('Models'));
            }
        );
    }

    /**
     * @param  string      $package_name
     * @param  string      $directory
     * @param  string|null $domain
     * @return Collection|null
     */
    public function getPossibleModels(string $package_name, string $directory, string $domain = null): ?Collection
    {
        return $this->getCache(
            $this->getTags($package_name, $domain),
            'models',
            function () use ($package_name, $directory, $domain) {
                $possibleModels = collect();

                if ($domain &&
                    Str::contains($directory, $domain) &&
                    $path = guess_file_or_directory_path(
                        Str::of($directory)->before($domain)->append($domain)->jsonSerialize(),
                        'Models',
                        false,
                        1
                    )
                ) {
                    $possibleModels = $possibleModels->merge(collect_classes_from_path($path));
                }

                if (Str::contains($directory, $package_name) &&
                    $path = guess_file_or_directory_path(
                        Str::of($directory)->before($package_name)->append($package_name)->jsonSerialize(),
                        'Models',
                        false,
                        1
                    )
                ) {
                    $possibleModels = $possibleModels->merge(collect_classes_from_path($path));
                }

                return $possibleModels->merge($this->getDefaultPossibleModels())
                    ->mapWithKeys(
                        function ($item) {
                            $key = Str::of($item)->afterLast('\\')->jsonSerialize();
                            return [$item => $key];
                        }
                    )->mapToGroups(
                        function ($item, $key) {
                            return [$item => $key];
                        }
                    );
            }
        );
    }

    /**
     * @param  string      $package_name
     * @param  string      $directory
     * @param  array       $policy_map
     * @param  string|null $domain
     * @return Collection|null
     */
    public function getPolicies(
        string $package_name,
        string $directory,
        array $policy_map,
        string $domain = null
    ): ?Collection {
        return $this->getModelRelatedMap('Policy', $package_name, $directory, $policy_map, $domain);
    }

    /**
     * @param  string      $package_name
     * @param  string      $directory
     * @param  array       $observer_map
     * @param  string|null $domain
     * @return Collection|null
     */
    public function getObservers(
        string $package_name,
        string $directory,
        array $observer_map,
        string $domain = null
    ): ?Collection {
        return $this->getModelRelatedMap('Observer', $package_name, $directory, $observer_map, $domain);
    }

    /**
     * @param  string      $package_name
     * @param  string      $directory
     * @param  array       $repository_map
     * @param  string|null $domain
     * @return Collection|null
     */
    public function getRepositories(
        string $package_name,
        string $directory,
        array $repository_map,
        string $domain = null
    ): ?Collection {
        return $this->getModelRelatedMap('Repository', $package_name, $directory, $repository_map, $domain);
    }

    /**
     * @param  string           $file_type
     * @param  string           $package_name
     * @param  string           $directory
     * @param  Collection|array $map
     * @param  string|null      $domain
     * @return Collection|null
     */
    private function getModelRelatedMap(
        string $file_type,
        string $package_name,
        string $directory,
        Collection|array $map,
        string $domain = null
    ): ?Collection {
        $type = Str::of($file_type);

        return $this->getCache(
            $this->getTags($package_name, $domain),
            $type->plural()->snake(),
            function () use ($map, $type, $domain, $package_name, $directory) {
                if (file_exists($directory)) {
                    $map = collect($map);
                    $classes = collect_classes_from_path($directory, $type->studly())
                    ?->mapWithKeys(fn($item, $key) => [$item => $key]);
                    $classes = $classes->merge($map->only($classes->keys()->toArray()));
                    $classesForGuessing = $classes->except($map->keys()->toArray());
                    if ($classesForGuessing->count() &&
                        $possibleModels = $this->getPossibleModels($package_name, $directory, $domain)) {
                        $classesForGuessing = $classesForGuessing->map(
                            function ($item) use ($possibleModels) {
                                return $possibleModels->get($item);
                            }
                        );
                        $classes = $classes->merge($classesForGuessing);
                    }

                    return $classes;
                }
                return null;
            }
        );
    }

    /*****
     * USER MODEL
     *****/

    /**
     * @return string|null
     */
    public function getUserModel(): ?string
    {
        if (class_exists($model = config('starter-kit.user_model')) && is_eloquent_model($model)) {
            return $model;
        }

        return null;
    }

    /**
     * @return Builder|null
     */
    public function getUserQueryBuilder(): ?Builder
    {
        if ($model = $this->getUserModel()) {
            return call_user_func($model . '::query');
        }

        return null;
    }
}
