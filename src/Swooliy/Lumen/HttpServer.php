<?php 

namespace Swooliy\Lumen;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Http Server  base on Swoole Http Server
 * 
 * @category Http_Server
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gamail.com>
 * @license  MIT 
 * @link     https://github.com/swooliy/swooliy-lumen
 */
class HttpServer extends AbstractHttpServer
{
    protected $host;

    protected $port;

    protected $name;

    protected $options;

    /**
     * Construct for HttpServer class
     */
    public function __construct()
    {
        if (!file_exists(base_path('config/swooliy.php'))) {
            $erroInfo = <<<END
Swooliy config file not created!
you should run php artisan swooliy:config
END;
            throw new Excption($erroInfo);
        }

        if (!(config('swooliy'))) {
            $erroInfo = <<<END
Swooliy config not add!
You should add 
\$app->configure('swooliy');
in bootstrap/app.php
END;
            throw new Exception($errInfo);
        }

        $this->host = config('swooliy.server.host');
        $this->port = config('swooliy.server.port');
        $this->name = config('swooliy.server.name');
        $this->options = config('swooliy.server.options');

        parent::__construct($this->host, $this->port, $this->options);
    }

    /**
     * Callback when swoole http server's master process created.
     *
     * @param Swoole\Http\Server $server swoole server instance
     * 
     * @return void 
     */
    public function onMasterStarted($server)
    {
        echo "{$this->name} server is starting at http://{$this->host}:{$this->port} on swoole\n";
                    
        // In MacOS, swoole can't set the process name
        if (PHP_OS != 'Darwin') {
            swoole_set_process_name("{$this->name}-master");
        }
    }

    /**
     * Callback when swoole http server's manager process created.
     *
     * @param Swoole\Http\Server $server swoole server instance
     * 
     * @return void 
     */
    public function onManagerStarted($server)
    {
        // In MacOS, swoole can't set the process name
        if (PHP_OS != 'Darwin') {
            swoole_set_process_name("{$this->name}-manager");
        }
    }

    /**
     * Callback when swoole http server's worker process created.
     *
     * @param Swoole\Http\Server $server   swoole server instance
     * @param int                $workerId current worker proccess's pid
     * 
     * @return void 
     */
    public function onWorkerStarted($server, $workerId)
    {
        if (PHP_OS != 'Darwin') {
            swoole_set_process_name("{$this->name}-worker-{$workerId}");
        }

        $server->app = include base_path("bootstrap/app.php");
    }

    /**
     * Callback when swoole http server's worker process received http messages.
     *
     * @param Swoole\Http\Request  $swRequest  urrent swoole request instance
     * @param Swoole\Http\Response $swResponse current swoole response instance
     * 
     * @return void 
     */
    public function onRequest($swRequest, $swResponse)
    {
        if ($swRequest->server['request_method'] == 'GET') {
            if (isset($swRequest->get) && count($swRequest->get) > 0) {
                $queryFields = array_except(
                    $swRequest->get,
                    [
                        'timestamp',
                        'sign'
                    ]
                );
                $qStr = http_build_query($queryFields);
                $cacheKey = $swRequest->server['request_uri'] . '?' . $qStr;
            } else {
                $cacheKey = $swRequest->server['request_uri'];
            }

            if (isset($this->server->memory[$cacheKey])) {
                var_dump("hit");
                $response = $this->server->memory[$cacheKey];
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

        $response = $this->server->app->handle(Request::capture());

        if ($swRequest->server['request_method'] == 'GET') {
            var_dump("cached");
            $this->server->memory[$cacheKey] = $response;
        }

        $swResponse->header("Content-Type", $response->header["Content-Type"] ?? "application/json");
        $swResponse->status($response->getStatusCode());
        $swResponse->end($response->getContent());
    }
}