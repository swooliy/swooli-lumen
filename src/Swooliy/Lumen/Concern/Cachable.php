<?php

namespace Swooliy\Lumen\Concern;

use Swooliy\MemoryCache\MemoryCache;

/**
 * Http Server  base on Swoole Http Server
 *
 * @category Http_Cache
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gamail.com>
 * @license  MIT https://github.com/swooliy/swooliy-lumen/LICENSE.md
 * @link     https://github.com/swooliy/swooliy-lumen
 */
trait Cachable
{

    /**
     * Cache instance
     *
     * @var Swooliy\MmemoryCache\MemoryCache
     */
    protected $cache;

    /**
     * Init the cache
     *
     * @return void
     */
    protected function initCache()
    {
        $this->cache = new MemoryCache(config('swooliy.cache.columns'));
    }

    /**
     * Can cache
     *
     * @param Swoole\Http\Request $request current request from swoole
     *
     * @return boolean
     */
    protected function canCache($request)
    {
        return $request->server['request_method'] == 'GET' &&
        !in_array($request->server['request_uri'], config('swooliy.cache.ingnore_apis'));
    }

    /**
     * Set Cache
     *
     * @param Swoole\Http\Request      $request  current request from swoole
     * @param Illumunate\Http\Response $response current response from lumen
     *
     * @return void
     */
    protected function setCache($request, $response)
    {
        $this->cache->set(
            $this->getCacheKey($request),
            [
                'status_code'  => $response->getStatusCode(),
                'content_type' => $response->header['Content-Type'] ?? "application/json",
                'content'      => $response->getContent(),
            ]
        );
    }

    /**
     * Has cache
     *
     * @param Swoole\Http\Request $request current request from swoole
     *
     * @return boolean
     */
    protected function hasCache($request)
    {
        if (!$this->canCache($request)) {
            return false;
        }

        $key = $this->getCacheKey($request);

        return $this->cache->get($key);

    }

    /**
     * Get cache key from current request
     *
     * @param Swoole\Http\Request $request current request from swoole
     *
     * @return string
     */
    protected function getCacheKey($request)
    {
        $uri = $request->server['request_uri'];

        if (isset($request->get) && count($request->get) > 0) {
            $queryFields = array_except(
                $request->get,
                config("swooliy.cache.ingnore_fields")
            );

            $qStr     = http_build_query($queryFields);
            $cacheKey = $uri . '?' . $qStr;
        } else {
            $cacheKey = $uri;
        }

        return $cacheKey;
    }
}
