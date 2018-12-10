<?php

namespace Swooliy\Lumen;

use Exception;
use Illuminate\Http\Request;
use Swooliy\Lumen\Concern\Cachable;
use Swooliy\Server\AbstractHttpServer;

/**
 * Http Server  base on Swoole Http Server
 *
 * @category Http_Server
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gamail.com>
 * @license  MIT https://github.com/swooliy/swooliy-lumen/LICENSE.md
 * @link     https://github.com/swooliy/swooliy-lumen
 */
class HttpServer extends AbstractHttpServer
{
    use Cachable;

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
            throw new Exception($erroInfo);
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

        $this->host    = config('swooliy.server.host');
        $this->port    = config('swooliy.server.port');
        $this->name    = config('swooliy.server.name');
        $this->options = config('swooliy.server.options');

        parent::__construct($this->host, $this->port, $this->options);

        $this->initCache();
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

        $this->server->app = include base_path("bootstrap/app.php");

    }

    /**
     * Callback when swoole http server's worker process received http messages.
     *
     * @param Swoole\Http\Request  $swRequest  current swoole request instance
     * @param Swoole\Http\Response $swResponse current swoole response instance
     *
     * @return void
     */
    public function onRequest($swRequest, $swResponse)
    {

        $data = $this->server->app['router'];

        file_put_contents(base_path("storage/logs/test"), $data);

        if ($response = $this->hasCache($swRequest)) {
            var_dump("hit");
            $swResponse->status($response['status_code']);
            $swResponse->end($response['content']);
            return;
        }

        if ($swRequest->server) {
            foreach ($swRequest->server as $key => $value) {
                $_SERVER[strtoupper($key)] = $value;
            }
        }

        $_GET    = $swRequest->get ?? [];
        $_POST   = $swRequest->post ?? [];
        $_COOKIE = $swRequest->cookie ?? [];
        $_FILES  = $swRequest->files ?? [];

        $response = $this->server->app->handle(Request::capture());

        if ($this->canCache($swRequest) && ($response->getStatusCode() == 200)) {
            var_dump("cached");
            $this->setCache($swRequest, $response);
        }

        // $swResponse->header("Content-Type", $response->header["Content-Type"] ?? "application/json");
        $swResponse->status($response->getStatusCode());
        $swResponse->end($response->getContent());
    }

    /**
     * Callback when swoole http server shutdown.
     *
     * @param Swoole\Http\Server $server swoole server instance
     *
     * @return void
     */
    public function onShutdown($server)
    {
        echo "The server has shutdown.\n";
    }

    /**
     * Callback when swoole http server's worker process stopped.
     *
     * @param Swoole\Http\Server $server   swoole server instance
     * @param int                $workerId the order number of the worker process
     *
     * @return void
     */
    public function onWorkerStopped($server, $workerId)
    {
        echo "The worker-{$workerId} has stopped.\n";
    }

    /**
     * Callback when swoole http server's worker process happen error.
     *
     * @param Swoole\Http\Server $server    swoole server instance
     * @param int                $workerId  the order number of the worker process
     * @param int                $workerPid the pid of the worker process
     * @param int                $exitCode  the status code return when the process exited
     * @param int                $signal    the signal when the process exited
     *
     * @return void
     */
    public function onWorkerError($server, $workerId, $workerPid, $exitCode, $signal)
    {
        echo "The worker-{$workerId} happend error, pid:{$workerPid}, exitCode:{$exitCode}, signal:{$signal}\n";
    }

    /**
     * Callback when swoole http server's manager process stopped.
     *
     * @param Swoole\Http\Server $server swoole server instance
     *
     * @return void
     */
    public function onManagerStopped($server)
    {
        echo "The manager process has stopped!\n";
    }
}
