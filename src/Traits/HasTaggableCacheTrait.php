<?php

namespace Fligno\StarterKit\Traits;

use Closure;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

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
     * @return mixed
     */
    private function getCache(array $tags, string $key, Closure $closure, bool $rehydrate = false): mixed
    {
        if ($this->isCacheTaggable()) {
            if ($rehydrate) {
                $this->forgetCache($tags, $key);
            }
            return Cache::tags($tags)->rememberForever($key, $closure);
        }

        return $closure();
    }

    /**
     * @param string $key
     * @param string[] $tags
     * @return bool
     */
    public function forgetCache(array $tags, string $key): bool
    {
        if ($this->isCacheTaggable()) {
            return Cache::tags($tags)->forget($key);
        }

        return false;
    }

    /**
     * @param string $class
     * @param string $base_class
     * @return void
     */
    public function validateClass(string &$class, string $base_class): void
    {
        $object = new $class();

        $class = get_class($object);

        if (! is_subclass_of($object, $base_class)) {
            throw new RuntimeException('Invalid ' . $base_class . ' class: ' . $class);
        }
    }
}
