<?php

namespace Swooliy\Lumen;

use Illuminate\Console\Command;

class ReloadLumenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swooliy:reload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reload lumen server by swooliy, php artisan swooliy:reload';

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

            exec("kill -USR1 {$pid}");

            $this->info("The server is reloaded success!");

        } catch (\Throwable $e) {

            die($e);
        
        }

    }
}
