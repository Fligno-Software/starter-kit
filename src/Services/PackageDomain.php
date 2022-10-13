<?php

namespace Fligno\StarterKit\Services;

use Exception;
use Fligno\StarterKit\Data\ServiceProviderData;
use Fligno\StarterKit\Traits\UsesProviderMorphMapTrait;
use Fligno\StarterKit\Traits\UsesProviderObserverMapTrait;
use Fligno\StarterKit\Traits\UsesProviderPolicyMapTrait;
use Fligno\StarterKit\Traits\UsesProviderRepositoryMapTrait;
use Fligno\StarterKit\Traits\UsesProviderRoutesTrait;
use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Throwable;

/**
 * Class PackageDomain
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
class PackageDomain
{
    use UsesProviderRoutesTrait;
    use UsesProviderMorphMapTrait;
    use UsesProviderObserverMapTrait;
    use UsesProviderPolicyMapTrait;
    use UsesProviderRepositoryMapTrait;

    /**
     * @var Collection|array
     */
    protected Collection|array $except_directories = [];

    /**
     * @var StarterKit|null
     */
    protected StarterKit|null $kit = null;

    /**
     * @var Application|null
     */
    protected Application|null $app = null;

    /**
     * @var ServiceProviderData|null
     */
    protected ServiceProviderData|null $provider_data = null;

    /**
     * @var Collection|null
     */
    protected Collection|null $existing_paths = null;

    /**
     * @param  ServiceProvider  $provider
     */
    public function __construct(protected ServiceProvider $provider)
    {
    }

    /**
     * @param  ServiceProvider  $provider
     * @return PackageDomain
     */
    public static function fromProvider(ServiceProvider $provider): PackageDomain
    {
        return new self($provider);
    }

    // Setters

    /**
     * @param  array|Collection  $except_directories
     */
    public function setExceptDirectories(array|Collection $except_directories): void
    {
        $this->except_directories = $except_directories;
    }

    /**
     * @param  array|Collection  $except_directories
     * @return $this
     */
    public function exceptDirectories(array|Collection $except_directories): static
    {
        $this->setExceptDirectories($except_directories);

        return $this;
    }

    /**
     * @return void
     */
    public function bootLaravelFiles(): void
    {
        callAfterResolvingStarterKit(function () {
            $this
                ->loadMorphMap()
                ->loadMigrations()
                ->loadViews() // Todo
                ->loadViewComponentsAs() // Todo
                ->loadRoutes()
                ->loadObservers()
                ->loadPolicies()
                ->loadRepositories();
        });
    }

    /**
     * @return void
     */
    public function registerLaravelFiles(): void
    {
        callAfterResolvingStarterKit(function (StarterKit $kit, Application $app) {
            // set the StarterKit instance to be used by load methods
            $this->kit = $kit;

            $this->provider_data = $kit->addToProviders($this->provider);

            // set Application instance to be used by loader methods
            $this->app = $app;

            // set existing paths to be used by
            $this->existing_paths = $kit->getPathsOnly(
                $this->provider_data->package,
                $this->provider_data->domain,
                $kit->getTargetDirectories()->diff($this->except_directories)->toArray()
            );

            $this
                ->loadTranslations() // Todo
                ->loadJsonTranslations() // Todo
                ->loadConfigs()
                ->loadHelpers();
        });
    }

    /***** HELPERS *****/

    /**
     * Load Helpers
     *
     * @return $this
     */
    protected function loadHelpers(): static
    {
        if ($this->existing_paths?->has(StarterKit::HELPERS_DIR)) {
            $this->kit->getHelpers($this->provider_data->package, $this->provider_data->domain)
                ->each(function ($item) {
                    file_exists($item['path']) && require $item['path'];
                });
        }

        return $this;
    }

    /***** CONFIGS *****/

    /**
     * Load Configs
     *
     * @return $this
     */
    protected function loadConfigs(): static
    {
        if (
            $this->existing_paths?->has(StarterKit::CONFIG_DIR) &&
            ! ($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())
        ) {
            $this->kit->getConfigs($this->provider_data->package, $this->provider_data->domain)
                ->each(function ($item) {
                    $path = $item['path'];

                    if (! file_exists($path)) {
                        return;
                    }

                    $config = $this->app->make('config');
                    $key = $item['name'];
                    $config->set($key, array_merge(
                        require $path, $config->get($key, [])
                    ));
                });
        }

        return $this;
    }

    /***** MIGRATIONS *****/

    /**
     * Register database migration paths.
     *
     * @return $this
     */
    protected function loadMigrations(): static
    {
        if ($this->existing_paths?->has(StarterKit::MIGRATIONS_DIR)) {
            $paths = $this->kit->getMigrationsPath($this->provider_data->package, $this->provider_data->domain);

            callAfterResolvingService('migrator', function (Migrator $migrator) use ($paths) {
                $paths->each(function ($path) use ($migrator) {
                    file_exists($path) && $migrator->path($path);
                });
            });
        }

        return $this;
    }

    /**
     * Todo: Views
     *
     * Register a view file namespace.
     *
     * @return $this
     */
    protected function loadViews(): static
    {
//        $this->callAfterResolving('view', function ($view) use ($path, $namespace) {
//            if (isset($this->app->config['view']['paths']) &&
//                is_array($this->app->config['view']['paths'])) {
//                foreach ($this->app->config['view']['paths'] as $viewPath) {
//                    if (is_dir($appPath = $viewPath.'/vendor/'.$namespace)) {
//                        $view->addNamespace($namespace, $appPath);
//                    }
//                }
//            }
//
//            $view->addNamespace($namespace, $path);
//        });

        return $this;
    }

    /**
     *  Todo: Views
     *
     * Register the given view components with a custom prefix.
     *
     * @return $this
     */
    protected function loadViewComponentsAs(): static
    {
//        $this->callAfterResolving(BladeCompiler::class, function ($blade) use ($prefix, $components) {
//            foreach ($components as $alias => $component) {
//                $blade->component($component, is_string($alias) ? $alias : null, $prefix);
//            }
//        });

        return $this;
    }

    /**
     *  Todo: Localization
     *
     * Register a translation file namespace.
     *
     * @return $this
     */
    protected function loadTranslations(): static
    {
//        $this->callAfterResolving('translator', function ($translator) use ($path, $namespace) {
//            $translator->addNamespace($namespace, $path);
//        });

        return $this;
    }

    /**
     * Todo: Localization
     *
     * Register a JSON translation file path.
     *
     * @return $this
     */
    protected function loadJsonTranslations(): static
    {
//        $this->callAfterResolving('translator', function ($translator) use ($path) {
//            $translator->addJsonPath($path);
//        });

        return $this;
    }

    /***** OBSERVERS *****/

    /**
     * Load Observers
     *
     * @return $this
     */
    protected function loadObservers(): static
    {
        if ($this->existing_paths?->has(StarterKit::OBSERVERS_DIR)) {
            $this->kit->getObservers($this->provider_data->package, $this->provider_data->domain, $this->getObserverMap())
                ->each(function ($model, $observer) {
                    if ($model instanceof Collection) {
                        $model = $model->first();
                    }
                    try {
                        call_user_func($model.'::observe', $observer);
                    } catch (Throwable) {
                        starterKit()->clearCache();
                    }
                }
                );
        }

        return $this;
    }

    /***** POLICIES *****/

    /**
     * Load Policies
     *
     * @return $this
     */
    protected function loadPolicies(): static
    {
        if ($this->existing_paths?->has(StarterKit::POLICIES_DIR)) {
            $this->kit->getPolicies($this->provider_data->package, $this->provider_data->domain, $this->getPolicyMap())
                ->each(function ($model, $policy) {
                    if ($model instanceof Collection) {
                        $model = $model->first();
                    }
                    try {
                        Gate::policy($model, $policy);
                    } catch (Exception) {
                        starterKit()->clearCache();
                    }
                }
                );
        }

        return $this;
    }

    /***** REPOSITORIES *****/

    /**
     * Load Repositories
     *
     * @return $this
     */
    protected function loadRepositories(): static
    {
        if ($this->existing_paths?->has(StarterKit::REPOSITORIES_DIR)) {
            $this->kit->getRepositories($this->provider_data->package, $this->provider_data->domain, $this->getRepositoryMap())
                ->each(function ($model, $repository) {
                    if ($model instanceof Collection) {
                        $model = $model->first();
                    }
                    try {
                        app()->when($repository)->needs(Builder::class)->give(fn (
                        ) => call_user_func($model.'::query'));
                    } catch (Exception) {
                        starterKit()->clearCache();
                    }
                }
                );
        }

        return $this;
    }

    /***** ROUTES *****/

    protected function loadRoutes(): static
    {
        if (
            $this->existing_paths?->has(StarterKit::ROUTES_DIR) &&
            ! ($this->app instanceof CachesRoutes && $this->app->routesAreCached())
        ) {
            $routes = $this->kit->getRoutes($this->provider_data->package, $this->provider_data->domain)
                ->filter(fn ($item) => file_exists($item['path']))
                ->when($this->shouldPrefixRouteWithDirectory(), function (Collection $collection) {
                    return $collection->map(function ($item) {
                        $append_to_prefix = Str::of($item['path'])
                            ->after('routes/')
                            ->before($item['file'])
                            ->trim('/')
                            ->jsonSerialize();
                        if ($append_to_prefix) {
                            $item[$this->append_key][] = $append_to_prefix;
                        }

                        return $item;
                    });
                })
                ->when($this->shouldPrefixRouteWithFileName(), function (Collection $collection) {
                    return $collection->map(function ($item) {
                        $append_to_prefix = $item['name'];
                        if (! in_array($append_to_prefix, ['api', 'web', 'console', 'channels'])) {
                            $item[$this->append_key][] = Str::of($append_to_prefix)
                                ->replace([' ', '.', '_'], '-') // replace space, dot, and underscore with dash
                                ->whenEndsWith('api', function (Stringable $str) {
                                    return $str->beforeLast('api');
                                })
                                ->rtrim('-')
                                ->jsonSerialize();
                        }

                        return $item;
                    });
                });

            // Separate api and non-api routes

            $webPaths = collect();

            $apiPaths = $routes->filter(function ($item) use ($webPaths) {
                $matches = preg_match('/api./', $item['file']);

                if (! $matches) {
                    $webPaths->add($item);
                }

                return $matches;
            });

            $apiPaths->each(function ($item) {
                $config = $this->getRouteApiConfiguration($item[$this->append_key] ?? null);
                Route::group($config, $item['path']);
            });

            $webPaths->each(function ($item) {
                $config = $this->getRouteWebConfiguration($item[$this->append_key] ?? null);
                Route::group($config, $item['path']);
            });
        }

        return $this;
    }

    /**
     * @param  array|null  $append_to_prefix
     * @return array
     */
    public function getRouteApiConfiguration(array $append_to_prefix = null): array
    {
        return $this->getRouteConfiguration(true, $append_to_prefix);
    }

    /**
     * @param  array|null  $append_to_prefix
     * @return array
     */
    public function getRouteWebConfiguration(array $append_to_prefix = null): array
    {
        return $this->getRouteConfiguration(false, $append_to_prefix);
    }

    /**
     * @param  bool  $is_api
     * @param  array|null  $append_to_prefix
     * @return string[]
     */
    public function getRouteConfiguration(bool $is_api, array $append_to_prefix = null): array
    {
        $config = [
            'middleware' => $is_api ? $this->getApiMiddleware() : $this->getWebMiddleware(),
            'prefix' => $this->getRoutePrefix(),
            'name' => null,
        ];

        // Prepare middleware

        if ($middleware = $is_api ? $this->getDefaultApiMiddleware() : $this->getDefaultWebMiddleware()) {
            $config['middleware'] = array_unique(array_merge($config['middleware'], $middleware));
        }

        // Middleware Group

        $middleware_group = $is_api ? 'api' : 'web';

        if (! in_array($middleware_group, $config['middleware'])) {
            $config['middleware'][] = $middleware_group;
        }

        // Prepare prefix and name

        if ($is_api) {
            $prefixes[] = 'api';
        }

        $prefixes[] = $config['prefix']; // Add previous prefix

        if ($append_to_prefix) {
            $prefixes[] = collect($append_to_prefix)->implode('/');
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

    /***** MORPH MAP *****/

    /**
     * @return $this
     */
    protected function loadMorphMap(): static
    {
        if (starterKit()->isMorphMapEnforced()) {
            enforceMorphMap($this->getMorphMap());
        }

        return $this;
    }
}
