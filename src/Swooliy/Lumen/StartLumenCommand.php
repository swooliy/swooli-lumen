<?php

namespace Swooliy\Lumen;

use \Illuminate\Http\Request;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Start lumen server
 * 
 * @category Artisan_Command
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gamail.com>
 * @license  MIT 
 * @link     https://github.com/swooliy/lumen
 */
class StartLumenCommand extends Command
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
    protected $description = 'Start lumen server by swooliy, php artisan swooliy:start';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            if (!class_exists('\swoole_http_server')) {
                $this->error("The command need php extension swoole");
                return;
            }

            if (!file_exists(base_path('config/swooliy.php'))) {
                $erroInfo = <<<END
Swooliy config file not created!
you should run php artisan swooliy:config
END;
                $this->error($erroInfo);
                return;
            }

            if (!(config('swooliy'))) {
                $erroInfo = <<<END
Swooliy config not add!
You should add 
\$app->configure('swooliy');
in bootstrap/app.php
END;
                $this->error($erroInfo);
                return;
            }

            $host = config('swooliy.server.host');
            $port = config('swooliy.server.port');
            $name = config('swooliy.server.name');

            $http = new \swoole_http_server($host, $port);

            $http->memory = [];

            $http->on(
                "start",
                function ($server) use ($host, $port, $name) {
                    $this->info("{$name} server is starting at http://{$host}:{$port} on swoole");
                    
                    // In MacOS, swoole can't set the process name
                    if (PHP_OS != 'Darwin') {
                        swoole_set_process_name("{$name}-master");
                    }
                }
            );

            $http->on(
                "managerStart",
                function ($server) use ($name) {
                    // In MacOS, swoole can't set the process name
                    if (PHP_OS != 'Darwin') {
                        swoole_set_process_name("{$name}-manager");
                    }
                }
            );

            $http->on(
                "workerStart",
                function ($server, $workerId) use ($name, $http) {
                    // In MacOS, swoole can't set the process name
                    if (PHP_OS != 'Darwin') {
                        swoole_set_process_name("{$name}-worker-{$workerId}");
                    }

                    $app = require base_path("bootstrap/app.php");

                    $server->app = $app;
                }
            );

            $http->on(
                "request",
                function ($swRequest, $swResponse) use ($http) {
                    if ($swRequest->server['request_method'] == 'GET') {
                        if (isset($swRequest->get) && count($swRequest->get) > 0) {
                            $queryFields = array_except(
                                $swRequest->get, [
                                    'timestamp', 
                                    'sign'
                                ]
                            );
                            $qStr = http_build_query($queryFields);
                            $cacheKey = $swRequest->server['request_uri'].'?'.$qStr;
                        } else {
                            $cacheKey = $swRequest->server['request_uri'];
                        }

                        if (isset($http->memory[$cacheKey])) {
                            var_dump("hit");
                            $response = $http->memory[$cacheKey];
                            $contentType = $response->header["Content-Type"] ?? "application/json";
                            $swResponse->header("Content-Type", $contentType);
                            $swResponse->status($response->getStatusCode());
                            $swResponse->end($response->getContent());
                            return;
                        }
                    }

                    if ($swRequest->server) {
                        foreach ($swRequest->server as $key => $value) {
                            $_SERVER[strtoupper($key)] = $value;
                        }
                    }

                    $_GET = $swRequest->get ?? [];
                    $_POST = $swRequest->post ?? [];
                    $_COOKIE = $swRequest->cookie ?? [];
                    $_FILES = $swRequest->files ?? [];

                    $response = $http->app->handle(Request::capture());

                    if ($swRequest->server['request_method'] == 'GET') {
                        var_dump("cached");
                        $http->memory[$cacheKey] = $response;
                    }

                    $swResponse->header("Content-Type", $response->header["Content-Type"] ?? "application/json");
                    $swResponse->status($response->getStatusCode());
                    $swResponse->end($response->getContent());
                }
            );

            $http->set(config('swooliy.server.options'));

            $options = [];
            foreach ($this->options() as $key => $value) {
                $options = array_add($options, '--' . $key, $value);
            }
            $jsonOptions = json_encode($options);
            file_put_contents(base_path("storage/logs/params"), $jsonOptions);

            $http->start();
        } catch (\Throwable $e) {
            die($e);
        }
    }
}
