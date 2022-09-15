<?php

namespace Fligno\StarterKit;

use Closure;
use Fligno\StarterKit\Traits\HasTaggableCacheTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class StarterKit
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 *
 * @since 2021-11-25
 */
class StarterKit
{
    use HasTaggableCacheTrait;

    /**
     * @var array
     */
    protected array $paths = [];

    /**
     * @var array
     */
    protected array $exception_renders = [];

    /**
     * @return string
     */
    public function getMainTag(): string
    {
        return 'starter-kit';
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        // Get copy from cache
        $this->paths = $this->getPaths()->toArray();
    }

    /**
     * @param  string|null  $package_name
     * @param  string|null  $domain_name
     * @param  bool  $rehydrate
     * @return Collection
     */
    public function getPaths(string $package_name = null, string $domain_name = null, bool $rehydrate = false): Collection
    {
        $tags = $this->getTags();
        $key = 'paths';

        $result = $this->getCache($tags, $key, fn () => collect($this->paths), $rehydrate);

        if ($package_name) {
            $package_name = str_replace('/', '.', $package_name);

            if ($domain_name) {
                $package_name = implode('.', [$package_name, 'domains', $domain_name]);
            }

            return collect(Arr::get($result, $package_name));
        }

        return $result;
    }

    /**
     * @param  string  $package_name
     * @param  string  $source_dir
     * @param  string|null  $domain
     * @param  array|null  $paths
     * @return bool
     */
    public function addToPaths(string $package_name, string $source_dir, string $domain = null, array &$paths = null): bool
    {
        [$vendor, $package] = explode('/', $package_name);

        if (! $paths) {
            $paths = $this->getPaths()->toArray();
        }

        // Check whether already exists
        if ((! $domain && isset($paths[$vendor][$package])) || ($domain && isset($paths[$vendor][$package]['domains'][$domain]))) {
            return false;
        }

        $targets = $this->getFilesFromPaths($source_dir);

        // Look for Domains
        if ($domains_path = guess_file_or_directory_path($source_dir, 'Domains')) {
            collect_files_or_directories($domains_path, true, false, true)
                ->each(function ($directory, $domain) use ($package_name, &$paths) {
                    $this->addToPaths($package_name, $directory, $domain, $paths);
                });
        }

        if ($domain) {
            $paths[$vendor][$package]['domains'][$domain]['path'] = $source_dir;
            $paths[$vendor][$package]['domains'][$domain]['directories'] = $targets;
        } else {
            $paths[$vendor][$package]['path'] = $source_dir;
            $paths[$vendor][$package]['directories'] = $targets;
            $this->paths = $paths;
            $this->getPaths(rehydrate: true);
        }

        return true;
    }

    /**
     * @return Collection
     */
    public function getTargetDirectories(): Collection
    {
        return collect([
            'database/migrations',
            'helpers',
            'routes',
            'Repositories',
            'Policies',
            'Observers',
            'Models',
            'tests',
        ]);
    }

    /**
     * @param  string  $source_dir
     * @return array
     */
    protected function getFilesFromPaths(string $source_dir): array
    {
        return guess_file_or_directory_path($source_dir, $this->getTargetDirectories())
            ->mapWithKeys(function ($path, $directory) {
                $files = match ($directory) {
                    'helpers' => collect_files_or_directories($path, false, true, true)->toArray(),
                    'Models' => collect_classes_from_path($path)
                        ->mapWithKeys(fn ($model) => [
                            $model => Str::of($model)->afterLast('\\')->jsonSerialize(), // App/Models/User => User
                        ])->toArray(),
                    'Repositories', 'Policies', 'Observers' => collect_classes_from_path($path, Str::of($directory)->singular()->studly()->jsonSerialize())->toArray(),
                    'routes' => collect(File::allFiles($path))->map(fn (SplFileInfo $info) => [
                        'file' => $info->getFilename(),
                        'path' => $info->getRealPath(),
                    ]),
                    default => null
                };

                $result[$directory]['path'] = $path;

                if ($files) {
                    $result[$directory]['files'] = $files;
                }

                return $result;
            })
            ->toArray();
    }

    /**
     * @param  string  $package_name
     * @param  string|null  $domain
     * @param  string|null  $dot_notation
     * @return Collection|null
     */
    public function getFromPaths(string $package_name, string $domain = null, string $dot_notation = null): ?Collection
    {
        if ($value = Arr::get($this->getPaths($package_name, $domain), $dot_notation)) {
            return collect($value);
        }

        return null;
    }

    /**
     * @param  string  $package_name
     * @param  string|null  $domain
     * @param  array  $except
     * @return Collection|null
     */
    public function getPathsOnly(string $package_name, string $domain = null, array $except = []): ?Collection
    {
        $tags = $this->getTags($package_name, $domain);
        $key = 'paths';

        return $this->getCache($tags, $key, function () use ($package_name, $domain, $except) {
            return $this->getFromPaths($package_name, $domain, 'directories')
                    ?->map(fn ($item) => $item['path'])
                    ->only($except);
        }
        );
    }

    /**
     * @param string|null $package_name
     * @return Collection|null
     */
    public function getDomains(string $package_name = null): ?Collection
    {
        if (is_null($package_name)) {
            $package_name = 'laravel/laravel';
        }

        return $this->getFromPaths($package_name, null, 'domains')?->map(fn ($value) => $value['path']);
    }

    /**
     * @param  string  $package_name
     * @param  string|null  $domain
     * @return Collection|null
     */
    public function getHelpers(string $package_name, string $domain = null): ?Collection
    {
        return $this->getFromPaths($package_name, $domain, 'directories.helpers.files');
    }

    /**
     * @param  string  $package_name
     * @param  string|null  $domain
     * @return Collection|null
     */
    public function getRoutes(string $package_name, string $domain = null): ?Collection
    {
        return $this->getFromPaths($package_name, $domain, 'directories.routes.files');
    }

    /**
     * @param  string  $package_name
     * @param  string|null  $domain
     * @return Collection|null
     */
    public function getModels(string $package_name, string $domain = null): ?Collection
    {
        return $this->getFromPaths($package_name, $domain, 'directories.Models.files');
    }

    /**
     * @return Collection|null
     */
    public function getDefaultPossibleModels(): ?Collection
    {
        return $this->getModels('laravel/laravel');
    }

    /**
     * @param  string  $package_name
     * @param  string|null  $domain
     * @return Collection|null
     */
    public function getPossibleModels(string $package_name, string $domain = null): ?Collection
    {
        $possibleModels = collect();

        // In getting possible models for Repository, Observer, and Policy files,
        // it should start from domain level, then to package level, then to root level.

        // Domain level
        if ($domain) {
            $possibleModels = $possibleModels->merge($this->getModels($package_name, $domain));
        }

        // Package level
        $possibleModels = $possibleModels->merge($this->getModels($package_name));

        // Root level
        $possibleModels = $possibleModels->merge($this->getDefaultPossibleModels());

        return $possibleModels->mapToGroups(
            function ($item, $key) {
                return [$item => $key];
            });
    }

    /**
     * @param  string  $directory
     * @param  string  $package_name
     * @param  string|null  $domain
     * @param  array  $map
     * @return Collection|null
     */
    public function getModelRelatedFiles(string $directory, string $package_name, string $domain = null, array $map = []): ?Collection
    {
        if ($files = $this->getFromPaths($package_name, $domain, 'directories.'.$directory.'.files')) {
            $files = collect($files)->mapWithKeys(fn ($item, $key) => [$item => $key]);
            $map = collect($map)->only($files->keys());
            $files = $files->merge($map);
            $unmatched = $files->except($map->keys());

            if ($unmatched->count()) {
                $possible_models = $this->getPossibleModels($package_name, $domain);
                $unmatched = $unmatched->map(fn ($item) => $possible_models->get($item) ?? []);
                $files = $files->merge($unmatched);
            }

            return $files;
        }

        return null;
    }

    /**
     * @param  string  $package_name
     * @param  array  $policy_map
     * @param  string|null  $domain
     * @return Collection|null
     */
    public function getPolicies(string $package_name, string $domain = null, array $policy_map = []): ?Collection
    {
        return $this->getModelRelatedFiles('Policies', $package_name, $domain, $policy_map);
    }

    /**
     * @param  string  $package_name
     * @param  string|null  $domain
     * @param  array  $observer_map
     * @return Collection|null
     */
    public function getObservers(string $package_name, string $domain = null, array $observer_map = []): ?Collection
    {
        return $this->getModelRelatedFiles('Observers', $package_name, $domain, $observer_map);
    }

    /**
     * @param  string  $package_name
     * @param  string|null  $domain
     * @param  array  $repository_map
     * @return Collection|null
     */
    public function getRepositories(string $package_name, string $domain = null, array $repository_map = []): ?Collection
    {
        return $this->getModelRelatedFiles('Repositories', $package_name, $domain, $repository_map);
    }

    /***** EXCEPTION RELATED *****/

    /**
     * @param  string|object|null  $exception_class
     * @return Collection|Closure|callable|null
     */
    public function getExceptionRenders(string|object $exception_class = null): Collection|Closure|callable|null
    {
        $result = collect($this->exception_renders);

        if ($exception_class) {
            return $result->get(get_class_name_from_object($exception_class));
        }

        return $result;
    }

    /**
     * @param  string  $exception_class
     * @param  Closure|callable  $closure
     * @param  bool  $override
     * @return bool
     */
    public function addExceptionRender(string $exception_class, Closure|callable $closure, bool $override = false): bool
    {
        // Check whether already exists
        if ($this->getExceptionRenders()->has($exception_class) && ! $override) {
            return false;
        }

        $this->exception_renders[$exception_class] = $closure;

        return true;
    }

    /***** USER MODEL *****/

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
            return call_user_func($model.'::query');
        }

        return null;
    }

    /***** POLYMORPHIC MAP *****/

    /**
     * @return Collection
     */
    public function getMorphMap(): Collection
    {
        return collect(Relation::morphMap());
    }

    /**
     * @param  string  $model_name
     * @return string|null
     */
    public function getMorphMapKey(string $model_name): string|null
    {
        if (is_eloquent_model($model_name)) {
            return $this->getMorphMap()->mapWithKeys(fn ($item, $key) => [$item => $key])->get($model_name);
        }

        return null;
    }

    /***** ROUTE MIDDLEWARES *****/

    /**
     * @param  bool  $is_api
     * @return array
     */
    public function getRouteMiddleware(bool $is_api): array
    {
        $middleware = $is_api ? config('starter-kit.api_middleware') : config('starter-kit.web_middleware');

        if (is_string($middleware)) {
            return explode(',', $middleware);
        }

        return $middleware;
    }

    /***** OTHER METHODS *****/

    /**
     * @return bool
     */
    public function shouldOverrideExceptionHandler(): bool
    {
        return config('starter-kit.override_exception_handler');
    }

    /**
     * @return bool
     */
    public function isSentryEnabled(): bool
    {
        return config('starter-kit.sentry_enabled');
    }

    /**
     * @return bool
     */
    public function isSentryTestApiEnabled(): bool
    {
        return ! App::isProduction() && config('starter-kit.sentry_test_api_enabled');
    }

    /***** GIT HOOKS RELATED *****/

    /**
     * @return array
     */
    public function getGitHooks(): array
    {
        return config('git-hooks');
    }
}
