<?php

namespace SeoAnalyzer;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

class Cache
{
    /**
     * @var CacheInterface
     */
    public $adapter;

    public function __construct(string $adapterClass = null, $ttl = 300)
    {
        if (empty($adapterClass)) {
            $adapterClass = FilesystemAdapter::class;
        }
        $this->adapter = new $adapterClass('seoanalyzer', $ttl);
    }

    /**
     * @param string $key Cache key
     * @param callable $callback Function that return data to be cached if cache empty
     * @param int|null $ttl Cache time in seconds. If empty global Cache ttl is used.
     * @return mixed
     */
    public function remember(string $key, callable $callback, int $ttl = null)
    {
        $value = $this->get($key);
        if (empty($value)) {
            $value = $callback();
            if ($value !== false) {
                $this->set($key, $value, $ttl);
            }
        }
        return $value;
    }

    /**
     * Returns cached item or false it no cache found for that key.
     *
     * @param string $cacheKey
     * @return bool|mixed
     */
    public function get(string $cacheKey)
    {
        $value = false;
        try {
            $cachedItem = $this->adapter->getItem($cacheKey);
            $hasKey = $cachedItem->isHit();
        } catch (InvalidArgumentException $e) {
            return false;
        }
        if ($hasKey) {
            try {
                $value = $cachedItem->get();
            } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
                return false;
            }
        }
        return $value;
    }

    /**
     * Stores value in cache.
     *
     * @param string $cacheKey
     * @param $value
     * @param $ttl
     * @return bool
     */
    public function set(string $cacheKey, $value, $ttl = null): bool
    {
        try {
            $cacheItem = $this->adapter->getItem($cacheKey);
            $cacheItem->set($value)->expiresAfter($ttl);
            return $this->adapter->save($cacheItem);
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            return false;
        }
    }
}
