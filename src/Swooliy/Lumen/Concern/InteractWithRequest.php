<?php

namespace Swooliy\Lumen\Concern;

/**
 * Http request related functions
 *
 * @category Http_Request_Functions
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gamail.com>
 * @license  MIT https://github.com/swooliy/swooliy-lumen/LICENSE.md
 * @link     https://github.com/swooliy/swooliy-lumen
 */
trait InteractWithRequest
{

    /**
     * Handle static files
     *
     * @param Swoole\Http\Request  $swRequest  current swoole request instance
     * @param Swoole\Http\Response $swResponse current swoole response instance
     *
     * @return void|bool
     */
    public function handleStatic($swRequest, $swResponse)
    {
        $uri = $swRequest->server['request_uri'] ?? '';

        $extension = substr(strrchr($uri, '.'), 1);
        if ($extension && in_array($extension, ['php', 'htaccess', 'config'])) {
            return;
        }

        $filename = base_path("public") . $uri;
        if (!is_file($filename) || filesize($filename) === 0) {
            return;
        }

        $swResponse->status(200);

        // Need fileinfo extension
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $filename);
        //$mime = \mime_content_type($filename);
        if ($extension === 'js') {
            $mime = 'text/javascript';
        } elseif ($extension === 'css') {
            $mime = 'text/css';
        }

        $swResponse->header('Content-Type', $mime);
        $swResponse->sendfile($filename);

        return true;
    }

    /**
     * Init php global params from swoole request.
     *
     * @param Swoole\Http\Request $swRequest current swoole request instance
     *
     * @return void
     */
    protected function initGlobalParams($swRequest)
    {
        if ($swRequest->server) {
            foreach ($swRequest->server as $key => $value) {
                $_SERVER[strtoupper($key)] = $value;
            }
        }

        $_GET    = $swRequest->get ?? [];
        $_POST   = $swRequest->post ?? [];
        $_COOKIE = $swRequest->cookie ?? [];
        $_FILES  = $swRequest->files ?? [];
    }
}
