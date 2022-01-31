<?php

namespace Fligno\StarterKit\Providers;

use Fligno\StarterKit\Facades\StarterKit;
use Fligno\StarterKit\Interfaces\UsesConsoleKernelInterface;
use Fligno\StarterKit\Interfaces\UsesHttpKernelInterface;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use JsonException;
use ReflectionClass;
use function Composer\Autoload\includeFile;

/**
 * Class BaseStarterKitServiceProvider
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
abstract class BaseStarterKitServiceProvider extends ServiceProvider
{
    /**
     * Artisan Commands
     *
     * @var array
     */
    protected array $commands = [];

    /**
     * Polymorphism Morph Maps
     *
     * @var array
     */
    protected array $morph_map = [];

    // /**
    // * @var array
    // */
    // protected array $facade_aliases = [];

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Default Load Functions
        $this->loadMigrationsFrom($this->guessFileOrFolderPath('database/migrations'));

        // Load Routes
        if ($this->isRoutesEnabled()) {
            $this->loadRoutesFrom($this->guessFileOrFolderPath('routes/api.php'));
            $this->loadRoutesFrom($this->guessFileOrFolderPath('routes/web.php'));
        }

        // Custom Load Functions With Folder Guessing
        $this->loadRepositoriesFrom($this->guessFileOrFolderPath('Repositories'));
        $this->loadPoliciesFrom($this->guessFileOrFolderPath('Policies'));
        $this->loadObserversFrom($this->guessFileOrFolderPath('Observers'));

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        // Register Console Kernel & Http Kernel
        if ($this instanceof UsesConsoleKernelInterface) {
            $this->app->booted(function () {
                $this->registerToConsoleKernel(app(Schedule::class));
            });
        }

        if ($this instanceof UsesHttpKernelInterface) {
            $this->app->booted(function () {
                $this->registerToHttpKernel(app('router'));
            });
        }

        // For Polymorphism
        if ($this->isMorphMapEnabled()) {
            $this->enforceMorphMap($this->morph_map);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        // Load Helper Files
        $this->loadHelpersFrom($this->guessFileOrFolderPath('helpers'));

        // Todo: Facade Aliases
        // foreach ($this->facade_aliases as $key=> $value) {
        //     $this->app->alias($key, $value);
        // }
    }

    /**
     * Overriding to not cause error in case the path does not exist.
     * Register a translation file namespace.
     *
     * @param  string  $path
     * @param  string  $namespace
     * @return void
     */
    protected function loadTranslationsFrom($path, $namespace): void
    {
        if (file_exists($path)) {
            parent::loadTranslationsFrom($path, $namespace);
        }
    }

    /**
     * Overriding to not cause error in case the path does not exist.
     * Register a view file namespace.
     *
     * @param  string|array  $path
     * @param  string  $namespace
     * @return void
     */
    protected function loadViewsFrom($path, $namespace): void
    {
        if (file_exists($path)) {
            parent::loadViewsFrom($path, $namespace);
        }
    }

    /**
     * Load the given routes file if routes are not already cached.
     *
     * @param  string  $path
     * @return void
     */
    protected function loadRoutesFrom($path): void
    {
        if (file_exists($path)) {
            parent::loadRoutesFrom($path);
        }
    }

    /**
     * Overriding to not cause error in case the path does not exist.
     * Register database migration paths.
     *
     * @param  array|string  $paths
     * @return void
     */
    protected function loadMigrationsFrom($paths): void
    {
        collect((array) $paths)->each(function ($path) {
            if (file_exists($path)) {
                parent::loadMigrationsFrom($path);
            }
        });
    }


    /**
     * Map the repository files to respective models.
     *
     * @param string $repositoriesPath
     * @param string|null $modelsPath
     * @return void
     */
    protected function loadRepositoriesFrom(string $repositoriesPath, string $modelsPath = null): void
    {
        StarterKit::registerRepositories($repositoriesPath, $modelsPath);
    }

    /**
     * Map the policy files to respective models.
     *
     * @param string $policiesPath
     * @param string|null $modelsPath
     * @return void
     */
    protected function loadPoliciesFrom(string $policiesPath, string $modelsPath = null): void
    {
        StarterKit::registerPolicies($policiesPath, $modelsPath);
    }

    /**
     * Map the observer files to respective models.
     *
     * @param string $observersPath
     * @param string|null $modelsPath
     * @return void
     */
    protected function loadObserversFrom(string $observersPath, string $modelsPath = null): void
    {
        StarterKit::registerObservers($observersPath, $modelsPath);
    }

    protected function loadHelpersFrom(string $path): void
    {
        if ($helpers = get_files_or_directories($path)) {
            foreach ($helpers as $helper) {
                includeFile($path . '/' . $helper);
            }
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
     * @param string $folderName
     * @param int $maxLevelToGuess
     * @return string
     */
    public function guessFileOrFolderPath(string $folderName, int $maxLevelToGuess = 3): string
    {
        $extendingClassFileName = (new ReflectionClass(static::class))->getFileName(); //class that extends `BaseStarterKitServiceProvider`

        for ($level = 1; $level <= $maxLevelToGuess ; $level++)
        {
            if (file_exists($temp = dirname($extendingClassFileName, $level) . '/' . $folderName))
            {
                return $temp;
            }
        }

        return '';
    }

    /**
     * Define the morph map for polymorphic relations and require all morphed models to be explicitly mapped.
     *
     * @param array $map
     * @param bool $merge
     */
    public function enforceMorphMap(array $map, bool $merge = true): void
    {
        Relation::enforceMorphMap($map, $merge);
    }

    /**
     * Get composer.json contents
     *
     * @param string|null $key
     * @return Collection|mixed|null
     */
    public function getComposerJson(?string $key): mixed
    {
        if ($path = $this->guessFileOrFolderPath('composer.json', 3)) {
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
     * Get Package Name from composer.json
     *
     * @return string|null
     */
    public function getPackageNameFromComposerJson(): ?string
    {
        return $this->getComposerJson('name');
    }

    /**
     * @return bool
     */
    public function isRoutesEnabled(): bool
    {
        return config('starter-kit.routes_enabled');
    }

    /**
     * @return bool
     */
    public function isMorphMapEnabled(): bool
    {
        return config('starter-kit.enforce_morph_map');
    }
}
