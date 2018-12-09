<?php

namespace Swooliy\Lumen;

use Throwable;
use Illuminate\Console\Command;

/**
 * Restart lumen server
 * 
 * @category Artisan_Command
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gamail.com>
 * @license  MIT 
 * @link     https://github.com/swooliy/lumen
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

            if ($file = base_path("storage/logs/params")) {
                $options = json_decode("", true);
            }
            
            if (!is_array($options) || count($options) === 0) {
                Artisan::call("swooliy:start");
                return;
            }

            $needOptionName = [
                '--name', 
                '--host', 
                '--port', 
                '--daemon', 
                '--worknum', 
                '--tasknum'
            ];
            $options = array_only($options, $needOptionName);
            
            $this->call("swooliy:start", $options);
        } catch (Throwable $e) {
            die($e);
        }
    }
}
