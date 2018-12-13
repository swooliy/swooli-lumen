<?php

namespace Swooliy\Lumen\Console;

use Throwable;
use Illuminate\Console\Command;

/**
 * Restart lumen server
 * 
 * @category Artisan_Command
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gmail.com>
 * @license  MIT 
 * @link     https://github.com/swooliy/swooliy-lumen
 */
class RestartCommand extends Command
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
    protected $description = 'Restart lumen server by swooliy';

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
            
            $this->call("swooliy:start");
        } catch (Throwable $e) {
            die($e);
        }
    }
}
