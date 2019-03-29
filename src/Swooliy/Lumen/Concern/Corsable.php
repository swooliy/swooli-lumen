<?php

namespace Swooliy\Lumen\Concern;

/**
 * Enable CORS Request.
 *
 * @category CORS
 *
 * @author  ney <zoobile@gmail.com>
 * @license MIT https://github.com/swooliy/swooliy-lumen/LICENSE.md
 *
 * @see https://github.com/swooliy/swooliy-lumen
 */
trait Corsable
{
    /**
     * Process the cors request.
     *
     * @param Swoole\Http\Request  $request  current request from swoole
     * @param Swoole\Http\Response $response current response from swoole
     *
     * @return Swoole\Http\Response $response
     */
    public function enableCrossRequest($request, $response)
    {
        $origin = $request->header['origin'] ?? '';

        if (in_array($origin, config('swooliy.cors'))) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Methods', 'OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'x-requested-with,session_id,Content-Type,token,Origin');
            $response->header('Access-Control-Max-Age', '86400');
            $response->header('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
