<?php

namespace Swooliy\Lumen;

use Throwable;
use Illuminate\Console\Command;

/**
 * Config lumen server
 * 
 * @category Artisan_Command
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gamail.com>
 * @license  MIT 
 * @link     https://github.com/swooliy/swooliy-lumen
 */
class ConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swooliy:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Config lumen server by swooliy';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $sourceConfigPath = __DIR__ . "/../../../config/swooliy.php";
            $targetConfigPath = base_path("config/swooliy.php");

            if (file_exists($targetConfigPath)) {
                $this->info("Swooliy config file alreay exists");
                return;
            }

            if (!file_exists(base_path("config"))) {
                @mkdir(base_path("config"));
            }

            if (!copy($sourceConfigPath, $targetConfigPath)) {
                $this->error("Make swooliy config file failured!");
                return;
            }


            $successInfo = <<<END
Make swooliy config file success!
Don't forget add 
\$app->configure('swooliy');
in bootstrap/app.php
END;
            $this->info($successInfo);
        } catch (Throwable $e) {
            die($e);
        }

    }
}
