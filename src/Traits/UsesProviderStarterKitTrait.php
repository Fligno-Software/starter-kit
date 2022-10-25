<?php

namespace Fligno\StarterKit\Traits;

use Fligno\StarterKit\Services\PackageDomain;
use Fligno\StarterKit\Services\StarterKit;
use Illuminate\Support\Collection;

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
    use UsesProviderRoutesTrait;
    use UsesProviderEnvVarsTrait;

    /**
     * Artisan Commands
     *
     * @var array
     */
    protected array $commands = [];

    /**
     * @var PackageDomain
     */
    protected PackageDomain $package_domain;

    /**
     * @return void
     */
    protected function instantiatePackageDomain(): void
    {
        $this->package_domain = PackageDomain::fromProvider($this);
        $this->preparePackageDomain($this->package_domain);
    }

    /**
     * @param  PackageDomain  $package_domain
     * @return void
     */
    protected function preparePackageDomain(PackageDomain $package_domain): void
    {
        $package_domain
            // General
            ->exceptDirectories($this->getExceptTargetDirectories())
            ->morphMap($this->getMorphMap())

            // Routes Related
            ->routePrefix($this->getRoutePrefix())
            ->prefixRouteWithFileName($this->shouldPrefixRouteWithFileName())
            ->prefixRouteWithDirectory($this->shouldPrefixRouteWithDirectory())
            ->webMiddleware($this->getWebMiddleware())
            ->apiMiddleware($this->getApiMiddleware())
            ->defaultWebMiddleware($this->getDefaultWebMiddleware())
            ->defaultApiMiddleware($this->getDefaultApiMiddleware())

            // Observers Related
            ->observerMap($this->getObserverMap())

            // Policys Related
            ->policyMap($this->getPolicyMap())

            // Repositories Related
            ->repositoryMap($this->getRepositoryMap());
    }

    /**
     * @return PackageDomain
     */
    public function getPackageDomain(): PackageDomain
    {
        return $this->package_domain;
    }

    /**
     * @return Collection
     */
    protected function getExceptTargetDirectories(): Collection
    {
        return collect()
            ->when(! $this->areConfigsEnabled(), fn ($collection) => $collection->push(StarterKit::CONFIG_DIR))
            ->when(! $this->areMigrationsEnabled(), fn ($collection) => $collection->push(StarterKit::MIGRATIONS_DIR))
            ->when(! $this->areHelpersEnabled(), fn ($collection) => $collection->push(StarterKit::HELPERS_DIR))
            ->when(! $this->areTranslationsEnabled(), fn ($collection) => $collection->push(StarterKit::LANG_DIR))
            ->when(! $this->areRoutesEnabled(), fn ($collection) => $collection->push(StarterKit::ROUTES_DIR))
            ->when(! $this->areRepositoriesEnabled(), fn ($collection) => $collection->push(StarterKit::REPOSITORIES_DIR))
            ->when(! $this->arePoliciesEnabled(), fn ($collection) => $collection->push(StarterKit::POLICIES_DIR))
            ->when(! $this->areObserversEnabled(), fn ($collection) => $collection->push(StarterKit::OBSERVERS_DIR));
    }

    /***** OVERRIDABLE METHODS *****/

    /**
     * Console-specific booting.
     *
     * @return void
     */
    abstract protected function bootForConsole(): void;

    /***** HELPER FILES RELATED *****/

    /**
     * @return bool
     */
    public function areHelpersEnabled(): bool
    {
        return true;
    }

    /***** TRANSLATIONS RELATED *****/

    /**
     * @return bool
     */
    public function areTranslationsEnabled(): bool
    {
        return config('starter-kit.translations_enabled', true);
    }

    /***** CONFIGS RELATED *****/

    /**
     * @return bool
     */
    public function areConfigsEnabled(): bool
    {
        return config('starter-kit.configs_enabled', true);
    }

    /***** MIGRATIONS RELATED *****/

    /**
     * @return bool
     */
    public function areMigrationsEnabled(): bool
    {
        return config('starter-kit.migrations_enabled', true);
    }
}
