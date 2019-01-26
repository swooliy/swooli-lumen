<?php

namespace Swooliy\Lumen\Concern;

use Swoole\Http\Request as SwooleRequest;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Http request related functions
 *
 * @category Http_Request_Functions
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gmail.com>
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
     * Instance a illuminate request from swoole request
     *
     * @param Swoole\Http\Request $swRequest current swoole request instance
     *
     * @return Illuminate\Http\Request
     */
    protected function makeIlluminateRequest(SwooleRequest $swRequest)
    {
        IlluminateRequest::enableHttpMethodParameterOverride();

        list($get, $post, $cookie, $files, $server, $content)
            = $this->toIlluminateParameters($swRequest);

        $request = $this->makeSymfonyRequest($get, $post, $cookie, $files, $server, $content);

        return IlluminateRequest::createFromBase($request);
    }

    /**
     * Transforms request parameters.
     *
     * @param \Swoole\Http\Request $request request
     * 
     * @return array
     */
    protected function toIlluminateParameters(SwooleRequest $request)
    {
        $get = isset($request->get) ? $request->get : [];
        $post = isset($request->post) ? $request->post : [];
        $cookie = isset($request->cookie) ? $request->cookie : [];
        $files = isset($request->files) ? $request->files : [];
        $header = isset($request->header) ? $request->header : [];
        $server = isset($request->server) ? $request->server : [];
        $server = $this->transformServerParameters($server, $header);
        $content = $request->rawContent();

        return [$get, $post, $cookie, $files, $server, $content];
    }

    /**
     * Transforms $_SERVER array.
     *
     * @param array $server server
     * @param array $header header
     * 
     * @return array
     */
    protected function transformServerParameters(array $server, array $header)
    {
        $__SERVER = [];

        foreach ($server as $key => $value) {
            $key = strtoupper($key);
            $__SERVER[$key] = $value;
        }

        foreach ($header as $key => $value) {
            $key = str_replace('-', '_', $key);
            $key = strtoupper($key);

            if (! in_array($key, ['REMOTE_ADDR', 'SERVER_PORT', 'HTTPS'])) {
                $key = 'HTTP_' . $key;
            }

            $__SERVER[$key] = $value;
        }

        return $__SERVER;
    }

    /**
     * Create Illuminate Request.
     *
     * @param array  $get     get  params
     * @param array  $post    post params
     * @param array  $cookie  cookie 
     * @param array  $files   files
     * @param array  $server  server 
     * @param string $content content
     * 
     * @return Symfony\Component\HttpFoundation\Request
     * @throws \Exception
     */
    protected function makeSymfonyRequest($get, $post, $cookie, $files, $server, $content = null)
    {
        /*
        |--------------------------------------------------------------------------
        | Copy from \Symfony\Component\HttpFoundation\Request::createFromGlobals().
        |--------------------------------------------------------------------------
        |
        | With the php's bug #66606, the php's built-in web server
        | stores the Content-Type and Content-Length header values in
        | HTTP_CONTENT_TYPE and HTTP_CONTENT_LENGTH fields.
        |
        */

        if ('cli-server' === PHP_SAPI) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $server)) {
                $server['CONTENT_LENGTH'] = $server['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $server)) {
                $server['CONTENT_TYPE'] = $server['HTTP_CONTENT_TYPE'];
            }
        }

        $request = new SymfonyRequest($get, $post, [], $cookie, $files, $server, $content);

        if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new ParameterBag($data);
        }

        return $request;
    }


}
