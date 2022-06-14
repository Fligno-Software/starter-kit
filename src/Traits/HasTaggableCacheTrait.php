<?php

namespace Fligno\StarterKit\Traits;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Trait HasTaggableCacheTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait HasTaggableCacheTrait
{
    /**
     * @return string
     */
    abstract function getMainTag(): string;

    /**
     * @return bool
     */
    public function isCacheTaggable(): bool
    {
        return method_exists(Cache::getStore(), 'tags');
    }

    /**
     * @return bool
     */
    public function clearCache(): bool
    {
        if ($this->isCacheTaggable()) {
            return Cache::tags($this->getMainTag())->flush();
        }

        return false;
    }

    /**
     * @param  string|null ...$tags
     * @return array
     */
    public function getTags(string|null ...$tags): array
    {
        return collect($this->getMainTag())->merge($tags)->filter()->toArray();
    }

    /**
     * @param  array   $tags
     * @param  $key
     * @param  Closure $closure
     * @return array|mixed
     */
    private function getCache(array $tags, $key, Closure $closure): mixed
    {
        if ($this->isCacheTaggable()) {
            return Cache::tags($tags)->rememberForever($key, $closure);
        }

        return $closure();
    }
}
