<?php

namespace Fligno\StarterKit\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use JsonException;

/**
 * Trait UsesProviderStarterKitTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesProviderStarterKitTrait
{
    use UsesProviderMorphMapTrait,
        UsesProviderObserverMapTrait,
        UsesProviderPolicyMapTrait,
        UsesProviderRepositoryMapTrait,
        UsesProviderDynamicRelationshipsTrait,
        UsesProviderHttpKernelTrait,
        UsesProviderConsoleKernelTrait;

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
    protected ?string $package_directory = null;

    /**
     * @var string|null
     */
    protected ?string $package_name = null;

    /**
     * @return void
     */
    public function bootLaravelFilesAndDomains(): void
    {
        $this->bootLaravelFiles($this->getBasePath());

        // Load Domains
        if (($dir = $this->getBasePath()) && $domains = starterKit()->getDomains($this->package_name, $dir)) {
            $domains->each(fn($directory, $key) => $this->bootLaravelFiles($directory, $key));
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

        $this->package_name = $this->getComposerJson('name');

        $temp = Str::of($this->provider_directory);

        if ($this->package_name) {
            $this->package_directory = $temp->before($temp->after($this->package_name))->jsonSerialize();
        }
    }

    /**
     * @return string|null
     */
    protected function getBasePath(): ?string
    {
        return $this->package_directory;
    }

    /**
     * @return Collection
     */
    protected function getTargetFilesAndDirectories(): Collection
    {
        return starterKit()->getTargetDirectories($this->package_name, function () {
            return collect(['database/migrations'])
                ->when($this->areHelpersEnabled(), fn($collection) => $collection->push('helpers'))
                ->when(
                    ! $this->app->routesAreCached() && $this->areRoutesEnabled(),
                    fn($collection) => $collection->push('routes')
                )
                ->when($this->areRepositoriesEnabled(), fn($collection) => $collection->push('Repositories'))
                ->when($this->arePoliciesEnabled(), fn($collection) => $collection->push('Policies'))
                ->when($this->areObserversEnabled(), fn($collection) => $collection->push('Observers'));
        });
    }

    /***** LOAD FILES & CLASSES *****/

    /**
     * @param object|string|null $sourceObjectOrClassOrDir
     * @param string|null $domain
     * @param bool $traverseUp
     * @param int $maxLevels
     * @return void
     */
    protected function bootLaravelFiles(
        object|string $sourceObjectOrClassOrDir = null,
        string $domain = null,
        bool $traverseUp = false,
        int $maxLevels = 3
    ): void {
        if (empty($sourceObjectOrClassOrDir)) {
            return;
        }

        $targets = $this->getTargetFilesAndDirectories();

        $directories = starterKit()->getTargetDirectoriesPaths(
            $this->package_name,
            $sourceObjectOrClassOrDir,
            $targets,
            $domain,
            $traverseUp,
            $maxLevels
        );

        // Load Migrations
        if ($path = $directories->get('database/migrations')) {
            $this->loadMigrationsFrom($path);
        }

        // Load Helpers
        if (($path = $directories->get('helpers')) &&
            $helpers = starterKit()->getHelpers($this->package_name, $path, $domain)) {
            $this->loadHelpersFrom($helpers);
        }

        // Load Routes
        if (($path = $directories->get('routes')) &&
            $routes = starterKit()->getRoutes($this->package_name, $path, $domain)) {
            $this->loadRouteFilesFrom($routes);
        }

        // Load Observers
        if (($path = $directories->get('Observers')) &&
            $observers = starterKit()->getObservers($this->package_name, $path, $this->observer_map, $domain)) {
            $this->loadObservers($observers);
        }

        // Load Policies
        if (($path = $directories->get('Policies')) &&
            $policies = starterKit()->getPolicies($this->package_name, $path, $this->policy_map, $domain)) {
            $this->loadPolicies($policies);
        }

        // Load Repositories
        if (($path = $directories->get('Repositories')) &&
            $repositories = starterKit()->getRepositories($this->package_name, $path, $this->repository_map, $domain)) {
            $this->loadRepositories($repositories);
        }
    }

    /**
     * Todo: Study on translation files
     *
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
     * @param Collection|null $collection
     * @return void
     */
    protected function loadHelpersFrom(Collection $collection = null): void
    {
        $collection?->each(function ($helper) {
            file_exists($helper) && require $helper;
        });
    }

    /**
     * @param Collection|null $collection
     * @return void
     */
    protected function loadRouteFilesFrom(Collection $collection = null): void
    {
        $collection?->each(function ($route) {
            if (file_exists($route)) {
                $config = Str::contains($route, 'api') ?
                    $this->getApiRouteConfiguration() :
                    $this->getWebRouteConfiguration();
                Route::group($config, function () use ($route) {
                    parent::loadRoutesFrom($route);
                });
            }
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
     * @param string|null $key
     * @return Collection|mixed|null
     */
    public function getComposerJson(?string $key): mixed
    {
        if ($path = guess_file_or_directory_path($this->provider_directory, 'composer.json', true)) {
            try {
                $collection = collect(json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR));

                if ($key) {
                    return $collection->get($key);
                }

                return $collection;
            } catch (JsonException) {
                return null;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function areHelpersEnabled(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function areRoutesEnabled(): bool
    {
        return config('starter-kit.routes_enabled');
    }

    /**
     * @return array
     */
    public function getApiRouteConfiguration(): array
    {
        return collect([
            'middleware' => config('starter-kit.api_guard'),
            'prefix' => 'api'
        ])->toArray();
    }

    /**
     * @return array
     */
    public function getWebRouteConfiguration(): array
    {
        return collect([
            'middleware' => config('starter-kit.web_guard'),
        ])->toArray();
    }
}
