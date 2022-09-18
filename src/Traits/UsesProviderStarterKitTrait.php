<?php

namespace Fligno\StarterKit\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Trait UsesProviderStarterKitTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesProviderStarterKitTrait
{
    use UsesProviderMorphMapTrait;
    use UsesProviderObserverMapTrait;
    use UsesProviderPolicyMapTrait;
    use UsesProviderRepositoryMapTrait;
    use UsesProviderDynamicRelationshipsTrait;
    use UsesProviderHttpKernelTrait;
    use UsesProviderConsoleKernelTrait;

    /**
     * Artisan Commands
     *
     * @var array
     */
    protected array $commands = [];

    /**
     * @var string|null
     */
    protected ?string $provider_directory = null;

    /**
     * @var string|null
     */
    protected ?string $package_path = null;

    /**
     * @var string|null
     */
    protected ?string $package_dir = null;

    /**
     * @var string|null
     */
    protected ?string $package_name = null;

    /**
     * @var string|null
     */
    protected ?string $vendor_name = null;

    /**
     * @return void
     */
    public function bootLaravelFilesAndDomains(): void
    {
        $dir = $this->getBasePath();

        $this->bootLaravelFiles($dir);

        // Load Domains
        if ($domains = starterKit()->getDomains($this->package_dir)) {
            $domains->each(fn ($path, $domain) => $this->bootLaravelFiles($path, $domain));
        }

        // For Console Kernel
        $this->bootConsoleKernel();

        // For Http Kernel
        $this->bootHttpKernel();

        // For Dynamic Relationships
        $this->bootDynamicRelationships();

        // For Polymorphism
        $this->bootMorphMap();

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /***** GETTERS & SETTERS *****/

    /**
     * @return void
     */
    protected function setProviderStarterKitFields(): void
    {
        $this->provider_directory = get_dir_from_object_class_dir($this);

        $this->package_dir = $this->getComposerJson('name');

        $temp = Str::of($this->provider_directory);

        if ($this->package_dir) {
            $this->package_path = $temp->before($temp->after($this->package_dir))->jsonSerialize();
            [$this->vendor_name, $this->package_name] = explode('/', $this->package_dir);
        }
    }

    /**
     * @return string|null
     */
    protected function getBasePath(): ?string
    {
        return $this->package_path;
    }

    /**
     * @return Collection
     */
    protected function getTargetFilesAndDirectories(): Collection
    {
        $diff = collect()
            ->when(! $this->areHelpersEnabled(), fn (Collection $collection) => $collection->push('helpers'))
            ->when($this->areTranslationsEnabled(), fn ($collection) => $collection->push('translations'))
            ->when(
                ! $this->app->routesAreCached() && $this->areRoutesEnabled(),
                fn ($collection) => $collection->push('routes')
            )
            ->when($this->areRepositoriesEnabled(), fn ($collection) => $collection->push('Repositories'))
            ->when($this->arePoliciesEnabled(), fn ($collection) => $collection->push('Policies'))
            ->when($this->areObserversEnabled(), fn ($collection) => $collection->push('Observers'));

        return starterKit()->getTargetDirectories()->diff($diff);
    }

    /***** LOAD FILES & CLASSES *****/

    /**
     * @param  object|string  $source_dir
     * @param  string|null  $domain
     * @return void
     */
    protected function bootLaravelFiles(object|string $source_dir, string $domain = null): void
    {
        starterKit()->addToPaths($this->package_dir, $source_dir, $domain);

        $directories = starterKit()->getPathsOnly($this->package_dir, $domain, $this->getTargetFilesAndDirectories()->toArray());

        // Load Migrations
        if ($path = $directories?->get('database/migrations')) {
            $this->loadMigrationsFrom($path);
        }

        // Load Helpers
        if ($directories?->has('helpers')) {
            $this->loadHelpersFrom(starterKit()->getHelpers($this->package_dir, $domain));
        }

        // Load Translations
        if ($directories?->has('resources/lang')) {
            $this->loadTranslationsFrom(starterKit()->getTranslations($this->package_dir, $domain), $this->package_name);
        }

        // Load Routes
        if ($directories?->has('routes')) {
            $this->loadRouteFilesFrom(starterKit()->getRoutes($this->package_dir, $domain));
        }

        // Load Observers
        if ($directories?->has('Observers')) {
            $this->loadObservers(starterKit()->getObservers($this->package_dir, $domain, $this->observer_map));
        }

        // Load Policies
        if ($directories?->has('Policies')) {
            $this->loadPolicies(starterKit()->getPolicies($this->package_dir, $domain, $this->policy_map));
        }

        // Load Repositories
        if ($directories?->has('Repositories')) {
            $this->loadRepositories(starterKit()->getRepositories($this->package_dir, $domain, $this->repository_map));
        }
    }

    /**
     * Overriding to not cause error in case the path does not exist.
     * Register a translation file namespace.
     *
     * @param  string|null  $path
     * @param  string|null  $namespace
     * @return void
     */
    protected function loadTranslationsFrom($path = null, $namespace = null): void
    {
        if (file_exists($path)) {
            parent::loadTranslationsFrom($path, $namespace);
        }
    }

    /**
     * Todo: Study on view files
     *
     * Overriding to not cause error in case the path does not exist.
     * Register a view file namespace.
     *
     * @param  string|array|null  $path
     * @param  string  $namespace
     * @return void
     */
    protected function loadViewsFrom($path = null, $namespace = null): void
    {
        if (is_array($path) || (is_string($path) && file_exists($path))) {
            parent::loadViewsFrom($path, $namespace);
        }
    }

    /**
     * @param  Collection|null  $collection
     * @return void
     */
    protected function loadHelpersFrom(Collection $collection = null): void
    {
        $collection?->each(function ($helper) {
            file_exists($helper['path']) && require $helper['path'];
        });
    }

    /**
     * @param  Collection|null  $collection
     * @return void
     */
    protected function loadRouteFilesFrom(Collection $collection = null): void
    {
        if (! $collection || ! $collection->count()) {
            return;
        }

        // Filter missing files
        $collection = $collection
            ->filter(fn ($item) => file_exists($item['path']))
            ->when($this->prefixDirectoryOnRoute(), function (Collection $collection) {
                return $collection->map(function ($item) {
                    if ($append_to_prefix = Str::of($item['path'])->after('routes/')->before($item['file'])->jsonSerialize()) {
                        $item['append_to_prefix'] = $append_to_prefix;
                    }

                    return $item;
                });
            })
            ->when($this->prefixFilenameOnRoute(), function (Collection $collection) {
                return $collection->map(function ($item) {
                    if (($append_to_prefix = Str::of($item['file'])->before('.')->jsonSerialize()) &&
                        ! in_array($append_to_prefix, ['api', 'web', 'console', 'channels'])
                    ) {
                        $item['append_to_prefix'] = isset($item['append_to_prefix']) ? $item['append_to_prefix'].$append_to_prefix : $append_to_prefix;
                    }

                    return $item;
                });
            });

        // Separate api and non-api routes

        $webPaths = collect();

        $apiPaths = $collection->filter(function ($item) use ($webPaths) {
            $matches = preg_match('/api./', $item['file']);

            if (! $matches) {
                $webPaths->add($item);
            }

            return $matches;
        });

        $apiPaths->each(function ($item) {
            $config = $this->getRouteApiConfiguration($item['append_to_prefix'] ?? null);
            Route::group($config, $item['path']);
        });

        $webPaths->each(function ($item) {
            $config = $this->getRouteWebConfiguration($item['append_to_prefix'] ?? null);
            Route::group($config, $item['path']);
        });
    }

    /**
     * Overriding to not cause error in case the path does not exist.
     * Register database migration paths.
     *
     * @param  array|string|null  $paths
     * @return void
     */
    protected function loadMigrationsFrom($paths = null): void
    {
        collect($paths)->each(function ($path) {
            if (file_exists($path)) {
                parent::loadMigrationsFrom($path);
            }
        });
    }

    /***** ABSTRACT METHODS *****/

    /**
     * Console-specific booting.
     *
     * @return void
     */
    abstract protected function bootForConsole(): void;

    /***** CUSTOM METHODS *****/

    /**
     * Get composer.json contents
     *
     * @param  string|null  $key
     * @return Collection|mixed|null
     */
    public function getComposerJson(string $key = null): mixed
    {
        // from this Provider class, traverse up and find the composer.json file
        $path = guess_file_or_directory_path($this->provider_directory, 'composer.json', true);

        $collection = getContentsFromComposerJson($path);

        if ($collection) {
            return $key ? $collection->get($key) : $collection;
        }

        return null;
    }

    /***** HELPER FILES RELATED *****/

    /**
     * @return bool
     */
    public function areHelpersEnabled(): bool
    {
        return true;
    }

    /***** ROUTES RELATED *****/

    /**
     * @return bool
     */
    public function areRoutesEnabled(): bool
    {
        return config('starter-kit.routes_enabled');
    }

    /***** ROUTES RELATED *****/

    /**
     * @return bool
     */
    public function areTranslationsEnabled(): bool
    {
        return config('starter-kit.routes_enabled');
    }

    /**
     * @return bool
     */
    public function prefixFilenameOnRoute(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function prefixDirectoryOnRoute(): bool
    {
        return false;
    }

    /**
     * @param  string|null  $append_to_prefix
     * @return array
     */
    public function getRouteApiConfiguration(string $append_to_prefix = null): array
    {
        return $this->getRouteConfiguration(true, $append_to_prefix);
    }

    /**
     * @param  string|null  $append_to_prefix
     * @return array
     */
    public function getRouteWebConfiguration(string $append_to_prefix = null): array
    {
        return $this->getRouteConfiguration(false, $append_to_prefix);
    }

    /**
     * @param  bool  $is_api
     * @param  string|null  $append_to_prefix
     * @return string[]
     */
    public function getRouteConfiguration(bool $is_api, string $append_to_prefix = null): array
    {
        $config = [
            'middleware' => $is_api ? $this->getRouteApiMiddleware() : $this->getRouteWebMiddleware(),
            'prefix' => $this->getRoutePrefix(),
            'name' => null,
        ];

        $middleware_group = $is_api ? 'api' : 'web';

        // Prepare middleware

        if ($middleware = $this->getDefaultRouteMiddleware($is_api)) {
            $config['middleware'] = array_unique(array_merge($config['middleware'], $middleware));
        }

        if (! in_array($middleware_group, $config['middleware'])) {
            $config['middleware'][] = $middleware_group;
        }

        // Prepare prefix and name

        if ($is_api) {
            $prefixes[] = 'api';
        }

        $prefixes[] = $config['prefix'];

        if ($append_to_prefix = trim($append_to_prefix, '/. ')) {
            $prefixes[] = $append_to_prefix;
        }

        $config['prefix'] = collect($prefixes)->filter()->implode('/');

        if ($config['prefix']) {
            $config['name'] = Str::of($config['prefix'])
                ->after('api')
                ->finish('/')
                ->ltrim('/')
                ->replace('/', '.')
                ->jsonSerialize();
        }

        return $config;
    }

    /**
     * @param  bool  $is_api
     * @return array
     */
    public function getDefaultRouteMiddleware(bool $is_api): array
    {
        return starterKit()->getRouteMiddleware($is_api);
    }

    /**
     * @return string|null
     */
    public function getRoutePrefix(): ?string
    {
        return null;
    }

    /**
     * @return array
     */
    public function getRouteWebMiddleware(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getRouteApiMiddleware(): array
    {
        return [];
    }
}
