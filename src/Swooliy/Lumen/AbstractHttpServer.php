<?php 

namespace Swooliy\Lumen;

use Exception;

/**
 * Abstract Http Server  base on Swoole Http Server
 * 
 * @category Http_Server
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gamail.com>
 * @license  MIT 
 * @link     https://github.com/swooliy/swooliy
 */
abstract class AbstractHttpServer
{
    protected $server;

    /**
     * Construct Abstract Http Server
     *
     * @param string     $host    server's host
     * @param string|int $port    server's port
     * @param array      $options server's options
     */
    public function __construct($host, $port, $options)
    {
        if (!class_exists('\swoole_http_server')) {
            throw new Exception("The command need php extension swoole");
        }

        $this->server = new \swoole_http_server($host, $port);

        $this->server->on('start', [$this, 'onMasterStarted']);
        $this->server->on('managerStart', [$this, 'onManagerStarted']);
        $this->server->on('workerStart', [$this, 'onWorkerStarted']);
        $this->server->on('request', [$this, 'onRequest']);

        $this->server->set($options);

        $this->server->memory = [];

    }

    /**
     * Start the server
     *
     * @return void
     */
    public function start()
    {
        $this->server->start();
    }

    /**
     * Callback when swoole http server's master process created.
     *
     * @param Swoole\Http\Server $server swoole server instance
     * 
     * @return void 
     */
    public abstract function onMasterStarted($server);

    /**
     * Callback when swoole http server's manager process created.
     *
     * @param Swoole\Http\Server $server swoole server instance
     * 
     * @return void 
     */
    public abstract function onManagerStarted($server);

    /**
     * Callback when swoole http server's worker process created.
     *
     * @param Swoole\Http\Server $server   swoole server instance
     * @param int                $workerId current worker proccess's pid
     * 
     * @return void 
     */
    public abstract function onWorkerStarted($server, $workerId);

    /**
     * Callback when swoole http server's worker process received http messages.
     *
     * @param Swoole\Http\Request  $swRequest  urrent swoole request instance
     * @param Swoole\Http\Response $swResponse current swoole response instance
     * 
     * @return void 
     */
    public abstract function onRequest($swRequest, $swResponse);


}