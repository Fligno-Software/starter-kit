<?php

namespace Fligno\StarterKit\Facades;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void setMainTag(string $main_tag)
 * @method static string getMainTag()
 * @method static bool clearCache()
 * @method static array getTags(string|null ...$tags)
 * @method static Collection|null getDomains(string $package_name, string $directory)
 * @method static Collection getTargetDirectories(string $package_name, Closure $callable)
 * @method static Collection|array|string|null getTargetDirectoriesPaths(string $package_name, object|string $sourceObjectOrClassOrDir, Collection|array|string $targetFileOrFolder, string $domain = null, bool $traverseUp = false, int $maxLevels = 3)
 * @method static Collection|null getHelpers(string $package_name, string $directory, string $domain = null)
 * @method static Collection|null getRoutes(string $package_name, string $directory, string $domain = null)
 * @method static Collection|null getDefaultPossibleModels()
 * @method static Collection|null getPossibleModels(string $package_name, string $directory, string $domain = null)
 * @method static Collection|null getPolicies(string $package_name, string $directory, array $policy_map, string $domain = null)
 * @method static Collection|null getObservers(string $package_name, string $directory, array $observer_map, string $domain = null)
 * @method static Collection|null getRepositories(string $package_name, string $directory, array $repository_map, string $domain = null)
 * @method static Collection|null getModelRelatedMap(string $file_type, string $package_name, string $directory, Collection|array $map, string $domain = null)
 * @see    \Fligno\StarterKit\StarterKit
 */
class StarterKit extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'starter-kit';
    }
}
