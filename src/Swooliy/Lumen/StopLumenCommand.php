<?php

namespace Swooliy\Lumen;

use Illuminate\Console\Command;

/**
 * Stop lumen server
 * 
 * @category Artisan_Command
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gamail.com>
 * @license  MIT 
 * @link     https://github.com/swooliy/lumen
 */
class StopLumenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swooliy:stop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stop lumen server by swooliy, php artisan swooliy:stop';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $pidFilePath = base_path("storage/logs/pid");

            if (!file_exists($pidFilePath)) {
                $this->info("Are you sure the lumen server is running?");
                return;
            }

            if (empty($pid = file_get_contents($pidFilePath))) {
                $this->info("Are you sure the lumen server is running?");
                return;
            }

            exec("kill {$pid}");

            file_put_contents($pidFilePath, "");

            $this->info("The server is stopped!");
        } catch (\Throwable $e) {
            die($e);
        }

    }
}
