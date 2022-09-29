<?php

namespace Fligno\StarterKit\Services;

use App\Models\Something;
use App\Providers\AppServiceProvider;
use Domains\OtherThing\Providers\OtherThingServiceProvider;
use Dummy\AlMighty\Domains\Something\Providers\SomethingServiceProvider;
use Dummy\AlMighty\Providers\AlMightyServiceProvider;
use Exception;
use Fligno\BoilerplateGenerator\Traits\UsesProviderRoutesTrait;
use Fligno\StarterKit\Traits\UsesProviderMorphMapTrait;
use Fligno\StarterKit\Traits\UsesProviderObserverMapTrait;
use Fligno\StarterKit\Traits\UsesProviderPolicyMapTrait;
use Fligno\StarterKit\Traits\UsesProviderRepositoryMapTrait;
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
     * @var string|null
     */
    protected ?string $provider_directory = null;

    /**
     * @var string|null
     */
    protected ?string $parent_directory = null;

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
     * @var string|null
     */
    protected ?string $domain_name = null;

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
     * @var Collection|null
     */
    protected Collection|null $existing_paths = null;

    /**
     * @param ServiceProvider $provider
     */
    public function __construct(protected ServiceProvider $provider)
    {
        $this->provider_directory = get_dir_from_object_class_dir($this->provider);

        $this->parent_directory = Str::of($this->provider_directory)
            ->before(StarterKit::DOMAINS_DIR)
            ->jsonSerialize();

        $this->package_dir = $this->guessComposerJsonFromProvider('name');

        $temp = Str::of($this->provider_directory);

        if ($this->package_dir) {
            $this->package_path = $temp->before($temp->after($this->package_dir))->jsonSerialize();
            [$this->vendor_name, $this->package_name] = explode('/', $this->package_dir);
        }

//        if ($this->provider instanceof SomethingServiceProvider) {
//            dd($this->provider_directory, $this->parent_directory, $this->package_dir, $this->package_path, $this->vendor_name, $this->package_name);
//        }
    }

    /**
     * @param ServiceProvider $provider
     * @return PackageDomain
     */
    public static function fromProvider(ServiceProvider $provider): PackageDomain
    {
        return new self($provider);
    }

    /**
     * Get composer.json contents
     *
     * @param  string|null  $key
     * @return Collection|mixed|null
     */
    public function guessComposerJsonFromProvider(string $key = null): mixed
    {
        // from this Provider class, traverse up and find the composer.json file
        // if from a domain, it's easier to find composer.json location by removing everything after domains
        $directory = Str::of($this->provider_directory)->before('domains')->jsonSerialize();
        $path = guess_file_or_directory_path($directory, 'composer.json', true);

        $collection = getContentsFromComposerJson($path);

        if ($collection) {
            return $key ? $collection->get($key) : $collection;
        }

        return null;
    }

    // Getters

    /**
     * @return ServiceProvider
     */
    public function getProvider(): ServiceProvider
    {
        return $this->provider;
    }

    /**
     * @return string|null
     */
    public function getProviderDirectory(): ?string
    {
        return $this->provider_directory;
    }

    /**
     * @return string|null
     */
    public function getPackagePath(): ?string
    {
        return $this->package_path;
    }

    /**
     * @return string|null
     */
    public function getPackageDir(): ?string
    {
        return $this->package_dir;
    }

    /**
     * @return string|null
     */
    public function getVendorName(): ?string
    {
        return $this->vendor_name;
    }

    /**
     * @return string|null
     */
    public function getPackageName(): ?string
    {
        return $this->package_name;
    }

    /**
     * @return string|null
     */
    public function getDomainName(): ?string
    {
        return $this->domain_name;
    }

    // Setters

    /**
     * @param array|Collection $except_directories
     */
    public function setExceptDirectories(array|Collection $except_directories): void
    {
        $this->except_directories = $except_directories;
    }

    /**
     * @param array|Collection $except_directories
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

            // add current package/domain to StarterKit
            $kit->addToPaths($this->getPackageDir(), $this->getPackagePath(), $this->getDomainName());

            // set Application instance to be used by loader methods
            $this->app = $app;

            // set existing paths to be used by
            $this->existing_paths = $kit->getPathsOnly(
                $this->getPackageDir(),
                $this->getDomainName(),
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
            $this->kit->getHelpers($this->getPackageDir(), $this->getDomainName())
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
            $this->kit->getConfigs($this->getPackageDir(), $this->getDomainName())
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
            $paths = $this->kit->getMigrationsPath($this->getPackageDir(), $this->getDomainName());

            callAfterResolvingService('migrator', function (Migrator $migrator) use ($paths) {
                $paths->each(function($path) use ($migrator) {
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
            $this->kit->getObservers($this->getPackageDir(), $this->getDomainName(), $this->getObserverMap())
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
            $this->kit->getPolicies($this->getPackageDir(), $this->getDomainName(), $this->getPolicyMap())
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
            $this->kit->getRepositories($this->getPackageDir(), $this->getDomainName(), $this->getRepositoryMap())
                ->each(function ($model, $repository) {
                    if ($model instanceof Collection) {
                        $model = $model->first();
                    }
                    try {
                        app()->when($repository)->needs(Builder::class)->give(fn(
                        ) => call_user_func($model . '::query'));
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
            $routes = $this->kit->getRoutes($this->getPackageDir(), $this->getDomainName())
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
     * @param bool $is_api
     * @param array|null $append_to_prefix
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


