<?php

namespace Swooliy\Lumen;

use Illuminate\Console\Command;
use function League\Flysystem\Adapter\file_get_contents;

class RestartLumenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swooliy:restart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restart lumen server by swooliy, php artisan swooliy:restart';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {

            $this->call("swooliy:stop");

            sleep(2);

            $options = json_decode(\file_get_contents(base_path("storage/logs/params")), true);

            if (count($options) === 0) {
                Artisan::call("swooliy:start");
                return;
            }

            $this->call("swooliy:start", array_only($options, ['--name', '--host', '--port', '--daemon', '--worknum', '--taskworknum']));
        
        } catch (\Throwable $e) {

            die($e);
        
        }
    }
}
