<?php

namespace Fligno\StarterKit\Traits;

use Fligno\StarterKit\Facades\StarterKit;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonException;

/**
 * Trait UsesProviderStarterKitTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesProviderStarterKitTrait
{
    use UsesProviderMorphMapTrait, UsesProviderDynamicRelationshipsTrait, UsesProviderHttpKernelTrait, UsesProviderConsoleKernelTrait;

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
        $this->bootLaravelFiles($this->package_directory);

        // Load Domains
        if (($dir = $this->getDomainsDirectory()) && $domainPath = guess_file_or_directory_path($dir, 'Domains')) {
            $this->bootDomainsFrom($domainPath);
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

    /**
     * @return void
     */
    public function registerLaravelFilesAndDomains(): void
    {
        // Load Helper Files
        $this->loadHelpersFrom(guess_file_or_directory_path($this->package_directory, 'helpers'));

        // Load Helpers inside Domains
        if (($dir = $this->getDomainsDirectory()) && $domainPath = guess_file_or_directory_path($dir, 'Domains')) {
            collect_files_or_directories($domainPath, true, false, true)?->each(function ($value) {
                $this->loadHelpersFrom(guess_file_or_directory_path($value, 'helpers'));
            });
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
    protected function getDomainsDirectory(): ?string
    {
        return $this->package_directory;
    }

    /***** LOAD FILES & CLASSES *****/

    /**
     * @param object|string|null $objectOrClassOrFolder
     * @param bool $shouldGoUp
     * @return void
     */
    protected function bootLaravelFiles(object|string $objectOrClassOrFolder = null, bool $shouldGoUp = false): void
    {
        if (empty($objectOrClassOrFolder)) {
            return;
        }

        // Default Load Functions
        $this->loadMigrationsFrom(guess_file_or_directory_path($objectOrClassOrFolder, 'database/migrations', $shouldGoUp));

        // Load Routes
        if ($this->isRoutesEnabled() && $path = guess_file_or_directory_path($objectOrClassOrFolder, 'routes', $shouldGoUp)) {
            collect_files_or_directories($path, false, true, true)?->each(fn($route) => $this->loadRoutesFrom($route));
        }

        // Custom Load Functions With Folder Guessing
        $this->loadRepositoriesFrom(guess_file_or_directory_path($objectOrClassOrFolder, 'Repositories', $shouldGoUp));
        $this->loadPoliciesFrom(guess_file_or_directory_path($objectOrClassOrFolder, 'Policies', $shouldGoUp));
        $this->loadObserversFrom(guess_file_or_directory_path($objectOrClassOrFolder, 'Observers', $shouldGoUp));
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
     * Load the given routes file if routes are not already cached.
     *
     * @param  string|null  $path
     * @return void
     */
    protected function loadRoutesFrom($path = null): void
    {
        if (file_exists($path)) {
            parent::loadRoutesFrom($path);
        }
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

    /**
     * Map the repository files to respective models.
     *
     * @param string|null $repositoriesPath
     * @param string|null $modelsPath
     * @return void
     */
    protected function loadRepositoriesFrom(string $repositoriesPath = null, string $modelsPath = null): void
    {
        StarterKit::registerRepositories($repositoriesPath, $modelsPath);
    }

    /**
     * Map the policy files to respective models.
     *
     * @param string|null $policiesPath
     * @param string|null $modelsPath
     * @return void
     */
    protected function loadPoliciesFrom(string $policiesPath = null, string $modelsPath = null): void
    {
        StarterKit::registerPolicies($policiesPath, $modelsPath);
    }

    /**
     * Map the observer files to respective models.
     *
     * @param string|null $observersPath
     * @param string|null $modelsPath
     * @return void
     */
    protected function loadObserversFrom(string $observersPath = null, string $modelsPath = null): void
    {
        StarterKit::registerObservers($observersPath, $modelsPath);
    }

    /**
     * @param string|null $path
     * @return void
     */
    protected function loadHelpersFrom(string $path = null): void
    {
        if ($path && $helpers = collect_files_or_directories($path, false, true, true)) {
            $helpers->each(function ($value) {
                include_once $value;
            });
        }
    }

    /**
     * @param string $path
     * @return void
     */
    protected function bootDomainsFrom(string $path): void
    {
        foreach (collect_files_or_directories($path, true, false, true) as $directory) {
            $this->bootLaravelFiles($directory);
        }
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
            }
            catch (JsonException) {
                return null;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isRoutesEnabled(): bool
    {
        return config('starter-kit.routes_enabled');
    }
}
