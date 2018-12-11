<?php

namespace Swooliy\Lumen\Concern;

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
     * @var Illuminate\Cache
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
     * @param Swoole\Http\Request $swRequest current request from swoole
     *
     * @return boolean
     */
    protected function canCache($swRequest)
    {
        return $swRequest->server['request_method'] == 'GET' &&
        isset(config('swooliy.cache.apis')[$swRequest->server['request_uri']]);
    }

    /**
     * Has cache
     *
     * @param Swoole\Http\Request $swRequest current request from swoole
     *
     * @return boolean|json
     */
    protected function hasCache($swRequest)
    {
        if (!$this->canCache($swRequest)) {
            return false;
        }

        $key = $this->getCacheKey($swRequest);

        if ($key) {
            return $this->cache->tags($key['tags'])->get($key['key']);
        }

    }

    /**
     * Get cache key from current request
     *
     * @param Swoole\Http\Request $swRequest current request from swoole
     *
     * @return string
     */
    protected function getCacheKey($swRequest)
    {
        $uri = $swRequest->server['request_uri'];

        if (isset(config("swooliy.cache.apis")[$uri])) {
            $info = config("swooliy.cache.apis")[$uri];

            $queryFields = array_only(
                $request->get,
                $info['fields']
            );

            $qStr     = http_build_query($queryFields);
            $key  = $uri . '?' . $qStr;
            $tags = $info['tags'];

            return compact('key', 'tags');
            
        }

    }
}
