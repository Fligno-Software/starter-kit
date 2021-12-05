<?php

namespace Fligno\StarterKit\Providers;

use Fligno\StarterKit\Facades\StarterKit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * Class AbstractStarterKitServiceProvider
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
abstract class AbstractStarterKitServiceProvider extends ServiceProvider
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
    protected array $morphMap = [];

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Default Load Functions
        $this->loadMigrationsFrom($this->guessFileOrFolderPath('database/migrations'));
        $this->loadRoutesFrom($this->guessFileOrFolderPath('routes/api.php'));
        $this->loadRoutesFrom($this->guessFileOrFolderPath('routes/web.php'));

        // Custom Load Functions With Folder Guessing
        $this->loadRepositoriesFrom($this->guessFileOrFolderPath('Repositories'));
        $this->loadPoliciesFrom($this->guessFileOrFolderPath('Policies'));
        $this->loadObserversFrom($this->guessFileOrFolderPath('Observers'));

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        // For Polymorphism
        $this->enforceMorphMap($this->morphMap);
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
        $extendingClassFileName = (new ReflectionClass(static::class))->getFileName(); //class that extends `AbstractStarterKitServiceProvider`

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
}
