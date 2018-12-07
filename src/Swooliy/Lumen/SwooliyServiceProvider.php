<?php 

namespace Swooliy\Lumen;

use Illuminate\Support\ServiceProvider;

class SwooliyServiceProvider extends ServiceProvider 
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                StartLumenCommand::class,
                StopLumenCommand::class,
                ReloadLumenCommand::class,
                RestartLumenCommand::class,
            ]);
        };
    }
}