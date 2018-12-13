<?php

namespace Swooliy\Lumen\Console;

use Throwable;
use Swooliy\Lumen\HttpServer;
use Laravel\Lumen\Application;
use Illuminate\Console\Command;

/**
 * Start lumen server
 * 
 * @category Artisan_Command
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gmail.com>
 * @license  MIT 
 * @link     https://github.com/swooliy/swooliy-lumen
 */
class StartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swooliy:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start lumen server by swooliy';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Application $app)
    {       
        try {
            (new HttpServer($app))->start();
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
