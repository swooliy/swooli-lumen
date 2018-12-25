<?php

namespace Swooliy\Lumen\Console;

use Throwable;
use Swooliy\Watcher\Watcher;
use Swooliy\Lumen\HttpServer;
use Illuminate\Console\Command;

/**
 * Watch command for lumen server
 * 
 * In watch mode, file updates trigger service reload, which is very suitable for use in development mode. 
 * It is not recommended to use this command in production environment.
 * 
 * @category Artisan_Command
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gmail.com>
 * @license  MIT 
 * @link     https://github.com/swooliy/swooliy-lumen
 */
class WatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swooliy:watch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Watch lumen server, when the file changed, auto-reload the server';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $watcherConfig = config("swooliy.watcher");

            if ($watcherConfig['enable'] != 1 || $watcherConfig['enable'] != true) {
                $this->error("Watch mode has been disabled");
                return;
            }

            // Ensure that the service is newly started
            if (HttpServer::isRunning()) {
                $this->call("swooliy:stop");
                sleep(1);
            }

            // The way to create a new process allows the wathing task to be executed as well
            $process = new \swoole_process(
                function () {
                    $this->call("swooliy:start");
                }, 
                true
            );

            $pid = $process->start();

            (new Watcher(
                $watcherConfig['files'] ?? [], 
                function (Watcher $watcher) use ($pid) {
                    posix_kill($pid, SIGUSR1);
                    $watcher->clear();

                    foreach ($watcher->files as $file) {
                        $watcher->watch($file, true);
                    }

                    $watcher->reloading = false;

                    $this->info("Reloaded!");
                }
            ))->run();
        } catch (Throwable $e) {
            die($e);
        }

    }
}
