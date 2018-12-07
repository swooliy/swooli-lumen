<?php

namespace Swooliy\Lumen;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends Command
{
    protected function configure()
    {

        $this
        // the name of the command (the part after "bin/console")
        ->setName('lumen:start')

            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'server host', '0.0.0.0')

            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'server port', 13140)

            ->addOption('daemon', 'd', InputOption::VALUE_NONE, 'server running in daemon mode')

            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'server name', 'lumen')

            ->addOption('worknum', null, InputOption::VALUE_OPTIONAL, 'server work num', 2)

            ->addOption('taskworknum', null, InputOption::VALUE_OPTIONAL, 'server task_work_num', 0)

        // the short description shown while running "php bin/console list"
            ->setDescription('Start a lumen service on swoole')

        // the full command description shown when running the command with
        // the "--help" option
            ->setHelp('--host, --port, --daemon, --name, --worknum, --taskworknum')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!\file_exists(getcwd() . "/bootstrap/app.php")) {
            $output->writeln("Are you sure you are in the lumen server's root work directory?");
            return;
        }

        $host = $input->getOption('host');
        $port = $input->getOption('port');
        $name = $input->getOption('name');

        $http = new \swoole_http_server($host, $port);

        $http->memory = [];

        $root = getcwd();

        $http->on("start", function ($server) use ($host, $port, $name) {

            echo "On master start.\n";

            echo ("{$name} server is starting at http://{$host}:{$port} on swoole\n");

            swoole_set_process_name("{$name}-master");

        });

        $http->on("managerStart", function ($server) use ($name) {

            echo "On manager start.\n";

            swoole_set_process_name("{$name}-manager");

        });

        $http->on("workerStart", function ($server, $workerId) use ($name, $root, $http) {

            echo "On worker: {$workerId} start.\n";

            swoole_set_process_name("{$name}-worker-{$workerId}");

            $server->app = require_once $root . "/bootstrap/app.php";

        });

        $http->on("request", function ($swooleRequest, $swooleResponse) use ($http) {
            if ($swooleRequest->server['request_method'] == 'GET') {
                if (count($swooleRequest->get) > 0) {
                    $queryString = http_build_query(array_except($swooleRequest->get, ['timestamp', 'sign']));
                    $cacheKey = $swooleRequest->server['request_uri'] . '?' . $queryString;
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

            $response->header("Content-Type", "application/json");
            $swooleResponse->status($response->getStatusCode());
            $swooleResponse->end($response->getContent());

        });

        $http->set([
            'daemonize'       => $input->getOption('daemon') ? true : false,
            "worker_num"      => (int) $input->getOption('worknum'),
            'task_worker_num' => (int) $input->getOption('taskworknum'),
            'log_file'        => $root . "/storage/logs/swoole.log",
            'pid_file'        => $root . "/storage/logs/pid",
            'max_conn'        => 10000, // if the system open files setting is 65536(show using `ulimit -a`), can reseting it by `ulimit -n 10000000`
        ]);

        $http->start();

    }
}
