<?php

namespace Swooliy\Lumen;

use Illuminate\Support\ServiceProvider;
use Swooliy\Lumen\Middleware\CacheMiddleware;

/**
 * The Lumen Service Provider for swooliy-lumen
 * 
 * @category Lumen_Service_Provider
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gmail.com>
 * @license  MIT 
 * @link     https://github.com/swooliy/swooliy-lumen
 */
class SwooliyServiceProvider extends ServiceProvider
{
    /**
     * Boot function for lumen service provider
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(CacheMiddleware::class);

        $this->app->routeMiddleware(
            [
                'api.cache' => CacheMiddleware::class,
            ]
        );

        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    Console\StartCommand::class,
                    Console\StopCommand::class,
                    Console\ReloadCommand::class,
                    Console\RestartCommand::class,
                    Console\ConfigCommand::class,
                    Console\WatchCommand::class,
                ]
            );
        };
    }
}