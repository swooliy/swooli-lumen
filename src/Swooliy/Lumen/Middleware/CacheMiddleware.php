<?php

namespace Swooliy\Lumen\Middleware;

use Cache;
use Closure;
use Exception;
use Swooliy\Lumen\Concern\SerializeResponse;

/**
 * Cache Midlleare for api response
 *
 * @category Middleware
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gmail.com>
 * @license  MIT https://github.com/swooliy/swooliy-lumen/LICENSE.md
 * @link     https://github.com/swooliy/swooliy-lumen
 */
class CacheMiddleware
{
    use SerializeResponse;

    protected $cacheTags = [];

    protected $cacheKey;

    protected $cacheFields = [];

    /**
     * Handle an incoming request.
     *
     * @param Illuminate\Http\Request $request current illuminate request
     * @param Closure                 $next    next route handler
     * @param string                  $tags    current middleware's param
     * @param string                  $fields  current middlware's param
     * 
     * @return mixed
     */
    public function handle($request, Closure $next, $tags, $fields=null)
    {
        if (config('swooliy.cache.switch') == 1) { 
            $this->cacheTags = explode("&", $tags);

            if ($fields) {
                $this->cacheFields = explode("&", $fields);
            }
            
            $uri = $request->path();
            $allRequestDatas  = http_build_query($request->only($this->cacheFields));

            if ($allRequestDatas) {
                $uri .= "?{$allRequestDatas}";
            }

            $this->cacheKey = $this->makeCacheKey($uri);
            
            if ($data = Cache::tags($this->cacheTags)->has($this->cacheKey)) {
                try {
                    return $this->unserialize(
                        Cache::tags($this->cacheTags)->get($this->cacheKey)
                    );
                } catch (Exception $e) {
                    return $next($request);
                }   
            }
        }

        return $next($request);
    }

    /**
     * Cache the response data when response end
     *
     * @param Illuminate\Http\Request  $request  current illuminate request
     * @param Illuminate\Http\Response $response current illuminate response
     * 
     * @return void
     */
    public function terminate($request, $response)
    {
        if (config('swooliy.cache.switch') == 1) {
            if (!Cache::tags($this->cacheTags)->has($this->cacheKey)) {
                Cache::tags($this->cacheTags)->forever(
                    $this->cacheKey, 
                    $this->serialize($response)
                );
            }
        }
    }

    /**
     * Make cache key
     *
     * @param string $uri current uri
     * 
     * @return string
     */
    protected function makeCacheKey($uri)
    {
        return 'route:' . $uri;
    }
}
