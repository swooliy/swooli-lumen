<?php

namespace Ney\SwooleCli\Lumen;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class StartCommand extends Command
{
    protected function configure()
    {
        
        $this
            // the name of the command (the part after "bin/console")
            ->setName('lumen:start')

            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'server host', '0.0.0.0')

            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'server port', 1314)

            ->addOption('daemon', 'd', InputOption::VALUE_NONE, 'server running in daemon mode')

            // the short description shown while running "php bin/console list"
            ->setDescription('Start a lumen service on swoole')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('--host, --port')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!\file_exists(getcwd(). "/bootstrap/app.php")) {
            $output->writeln("Are you sure you are in the lumen server's root work directory?");
            return;
        }

        $host = $input->getOption('host');
        $port = $input->getOption('port');

        $http = new \swoole_http_server($host, $port);

        $root = getcwd();

        $http->on("start", function($server) use ($host, $port) {
        
            echo("Lumen server is started at http://{$host}:{$port} on swoole\n");
            
        });
        
        $http->on("workerStart", function($server) use ($root) {
            
            $server->app = require_once $root . "/bootstrap/app.php";
            
        });
        
        $http->on("request", function($swooleRequest, $swooleResponse) use ($http) {
            
            if ($swooleRequest->server) {
                foreach($swooleRequest->server as $key => $value) {
                    $_SERVER[strtoupper($key)] = $value;
                }
            }
            $_GET = $swooleRequest->get ?? [];
            $_POST = $swooleRequest->post ?? [];
            $_COOKIE = $swooleRequest->cookie ?? [];
            $_FILES = $swooleRequest->files ?? [];
        
            $response = $http->app->handle(\Illuminate\Http\Request::capture()); 
        
            $swooleResponse->status($response->getStatusCode());
            $swooleResponse->end($response->getContent());
        
        });

        $http->set([
            'daemonize' => $input->getOption('daemon') ? true : false,
            'log_file' => $root . "/storage/logs/swoole.log",
            'pid_file' => $root . "/storage/logs/pid",
        ]);
        
        $http->start();
        
    }
}