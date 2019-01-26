<?php

namespace Swooliy\Lumen\Concern;

use Cache;
use Swooliy\Lumen\Middleware\ResponseSerializer;

/**
 * Http Server  base on Swoole Http Server
 *
 * @category Http_Cache
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gmail.com>
 * @license  MIT https://github.com/swooliy/swooliy-lumen/LICENSE.md
 * @link     https://github.com/swooliy/swooliy-lumen
 */
trait Cachable
{
    protected $serializer;

    protected $routes;

    protected $customRoutes;

    /**
     * Init cache instance
     *
     * @return void
     */
    protected function initCache()
    {
        $this->serializer = new ResponseSerializer;

        $this->routes = $this->app->router->getRoutes();

        $this->customRoutes = config("swooliy.cache.apis");
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
        $uri = $request->server['request_uri'];
        $cacheMiddleware = $this->getCacheMiddleware($uri);

        if ($cacheMiddleware) {
            $tags = $cacheMiddleware['tags'] ?? [];
            $key  = $this->getCacheKey($uri, $cacheMiddleware['fields'] ?? []);

            Cache::tags($tags)->forever(
                $key,
                $this->serializer->serialize($response)
            );
        }
    }

    /**
     * Has cache
     *
     * @param Swoole\Http\Request $request current request from swoole
     *
     * @return mixed
     */
    protected function hasCache($request)
    {
        if ($request->server['request_method'] != 'GET') {
            return false;
        }

        $uri = $request->server['request_uri'];
        $cacheMiddleware = $this->getCacheMiddleware($uri);

        if (!$cacheMiddleware) {
            return false;
        }

        $tags = $cacheMiddleware['tags'] ?? [];
        $key  = $this->getCacheKey($request->get ?? [], $uri, $cacheMiddleware['fields'] ?? []);

        if (Cache::tags($tags)->has($key)) {
            return $this->serializer->unserialize(Cache::tags($tags)->get($key));
        }

        return false;
    }

    /**
     * Get cache key from current request
     *
     * @param array  $query  current query data
     * @param string $uri    current request uri
     * @param string $fields current cache middleare fields
     *
     * @return string
     */
    protected function getCacheKey($query, $uri, $fields)
    {
        $uri = ltrim($uri, "/");
        $cacheKey = 'route:' . $uri;

        $queryFields = array_only(
            $query,
            $fields
        );

        $qStr = http_build_query($queryFields);

        if (!empty($qStr)) {
            $cacheKey = 'route:' . $uri . '?' . $qStr;
        }

        return $cacheKey;
    }

    /**
     * Get cache middleware info by uri
     *
     * @param string $uri current request uri
     * 
     * @return null|array
     */
    protected function getCacheMiddleware($uri)
    {
        $route = $this->routes['GET' . $uri] ?? null;

        $cacheMiddleware = null;

        if ($route) {
            $middlewares = $route['action']['middleware'] ?? [];

            if (count($middlewares) > 0) {
                foreach ($middlewares as $middleware) {
                    if (starts_with($middleware, 'api.cache')) {
                        $middlewareArr = explode(":", $middleware);
                        $tagsAndFields = explode(",", $middlewareArr[1]);
                        $cacheMiddleware = [
                            'tags'   => explode("&", $tagsAndFields[0]), 
                            'fields' => explode("&", $tagsAndFields[1]),
                        ];
                        break;
                    }
                }
        
            }
        }

        if (!$cacheMiddleware && isset($this->customRoutes[$uri])) {
            $cacheMiddleware = $this->customRoutes[$uri];
        }

        return $cacheMiddleware;
    }
}
