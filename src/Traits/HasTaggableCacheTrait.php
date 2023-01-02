<?php

namespace Fligno\StarterKit\Traits;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Cache\CacheManager;
use RuntimeException;
use Throwable;

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
    abstract public function getMainTag(): string;

    /**
     * @var CacheManager|null
     */
    protected CacheManager|null $cache_manager = null;

    /**
     * @return CacheManager
     */
    public function getCacheManager(): CacheManager
    {
        return $this->cache_manager ?? cache();
    }

    /**
     * @param  CacheManager|null  $cache_manager
     */
    public function setCacheManager(?CacheManager $cache_manager): void
    {
        $this->cache_manager = $cache_manager;
    }

    /**
     * @return bool
     */
    public function isCacheTaggable(): bool
    {
        try {
            return method_exists($this->getCacheManager()->getStore(), 'tags');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function clearCache(): bool
    {
        if ($this->isCacheTaggable()) {
            return $this->getCacheManager()->tags($this->getMainTag())->flush();
        }

        return false;
    }

    /**
     * @param ...$tags
     * @return array
     */
    public function getTags(...$tags): array
    {
        return collect($this->getMainTag())->merge($tags)->filter()->toArray();
    }

    /**
     * @param string[] $tags
     * @param string $key
     * @param Closure $closure
     * @param bool $rehydrate
     * @param DateTimeInterface|DateInterval|int|null $ttl
     * @return mixed
     */
    private function getCache(array $tags, string $key, Closure $closure, bool $rehydrate = false, DateTimeInterface|DateInterval|int $ttl = null): mixed
    {
        if ($this->isCacheTaggable()) {
            if ($rehydrate) {
                $this->forgetCache($tags, $key);
            }

            $taggedCache = $this->getCacheManager()->tags($tags);

            if ($ttl) {
                return $taggedCache->remember($key, $ttl, $closure);
            }

            return $taggedCache->rememberForever($key, $closure);
        }

        return $closure();
    }

    /**
     * @param  string  $key
     * @param  string[]  $tags
     * @return bool
     */
    public function forgetCache(array $tags, string $key): bool
    {
        if ($this->isCacheTaggable()) {
            return $this->getCacheManager()->tags($tags)->forget($key);
        }

        return false;
    }

    /**
     * @param  string  $class
     * @param  string  $base_class
     * @return void
     */
    public function validateClass(string &$class, string $base_class): void
    {
        $object = new $class();

        $class = get_class($object);

        if (! is_subclass_of($object, $base_class)) {
            throw new RuntimeException('Invalid '.$base_class.' class: '.$class);
        }
    }
}
