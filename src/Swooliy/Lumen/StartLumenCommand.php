<?php

namespace Swooliy\Lumen;

use Illuminate\Console\Command;

class StartLumenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swooliy:start
                                {--name=lumen : server name}
                                {--host=0.0.0.0 : server host}
                                {--port=13140 : server port},
                                {--daemon : server running in daemon mode}
                                {--worknum=2 : server worker number}
                                {--taskworknum=0 : server task worker num}
                            ';

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
            $host = $this->option('host');
            $port = $this->option('port');
            $name = $this->option('name');
    
            if (!class_exists('\swoole_http_server')) {
                $this->error("The command need php extension swoole");
                return;
            }
    
            $http = new \swoole_http_server($host, $port);
    
            $http->memory = [];
        
            $http->on("start", function ($server) use ($host, $port, $name) {
    
                $this->info("{$name} server is starting at http://{$host}:{$port} on swoole");
    
                swoole_set_process_name("{$name}-master");
    
            });
    
            $http->on("managerStart", function ($server) use ($name) {
    
                swoole_set_process_name("{$name}-manager");
    
            });
    
            $http->on("workerStart", function ($server, $workerId) use ($name, $http) {
        
                swoole_set_process_name("{$name}-worker-{$workerId}");

                var_dump(base_path("bootstrap/app.php"));

                var_dump(getcwd());

                $server->app = require_once base_path("bootstrap/app.php");
    
            });
    
            $http->on("request", function ($swooleRequest, $swooleResponse) use ($http) {
                if ($swooleRequest->server['request_method'] == 'GET') {
                    if (count($swooleRequest->get) > 0) {
                        $queryString = http_build_query(array_except($swooleRequest->get, ['timestamp', 'sign']));
                        $cacheKey    = $swooleRequest->server['request_uri'] . '?' . $queryString;
                    } else {
                        $cacheKey = $swooleRequest->server['request_uri'];
                    }
    
                    if (isset($http->memory[$cacheKey])) {
                        var_dump("hit");
                        $response = $http->memory[$cacheKey];
                        $response->header("Content-Type", "application/json");
                        $swooleResponse->status($response->getStatusCode());
                        $swooleResponse->end($response->getContent());
                        return;
                    }
                }
    
                if ($swooleRequest->server) {
                    foreach ($swooleRequest->server as $key => $value) {
                        $_SERVER[strtoupper($key)] = $value;
                    }
                }
    
                $_GET    = $swooleRequest->get ?? [];
                $_POST   = $swooleRequest->post ?? [];
                $_COOKIE = $swooleRequest->cookie ?? [];
                $_FILES  = $swooleRequest->files ?? [];
    
                $response = $http->app->handle(\Illuminate\Http\Request::capture());
    
                if ($swooleRequest->server['request_method'] == 'GET') {
                    var_dump("cached");
                    $http->memory[$cacheKey] = $response;
                }
    
                $response->header("Content-Type",  $response->header["Content-Type"] ?? "application/json");
                $swooleResponse->status($response->getStatusCode());
                $swooleResponse->end($response->getContent());
    
            });

            $http->set([
                'daemonize'       => $this->option('daemon'),
                "worker_num"      => (int) $this->option('worknum'),
                'task_worker_num' => (int) $this->option('taskworknum'),
                'log_file'        => base_path("storage/logs/swoole.log"),
                'pid_file'        => base_path("storage/logs/pid"),
                // if the system open files setting is 65536(show using `ulimit -a`), can reseting it by `ulimit -n 10000000`
                'max_conn'        => 10000,
            ]);

            $options = [];

            foreach($this->options() as $key => $value) {
                $options = array_add($options, '--' . $key, $value);
            }

            file_put_contents(base_path("storage/logs/params"), json_encode($options));

            $http->start();

        } catch (\Throwable $e) {
            die($e);
        }
       

    }
}
