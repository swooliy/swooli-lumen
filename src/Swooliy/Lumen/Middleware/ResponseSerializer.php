<?php

namespace Swooliy\Lumen\Middleware;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Response Serializer for api cache
 *
 * @category Response
 * @package  Swooliy\Lumen
 * @author   ney <zoobile@gmail.com>
 * @license  MIT https://github.com/swooliy/swooliy-lumen/LICENSE.md
 * @link     https://github.com/swooliy/swooliy-lumen
 */
class ResponseSerializer
{
    const RESPONSE_TYPE_NORMAL = 'response_type_normal';
    const RESPONSE_TYPE_FILE   = 'response_type_file';

    /**
     * Serialize
     *
     * @param Symfony\Component\HttpFoundation\Response $response current response
     * 
     * @return string
     */
    public function serialize(Response $response): string
    {
        return serialize($this->getResponseData($response));
    }

    /**
     * Unserialize
     *
     * @param string $serializedResponse the serialize string content
     * 
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function unserialize(string $serializedResponse): Response
    {
        $responseProperties = unserialize($serializedResponse);

        if (! $this->containsValidResponseProperties($responseProperties)) {
            throw new Exception('The response data is unvalid');
        }

        $response = $this->buildResponse($responseProperties);

        $response->headers = $responseProperties['headers'];

        return $response;
    }

    /**
     * Get the response data include statusCode, headers, content, type
     * 
     * @param Symfony\Component\HttpFoundation\Response $response the current response
     * 
     * @return array
     */
    protected function getResponseData(Response $response): array
    {
        $statusCode = $response->getStatusCode();
        $headers = $response->headers;

        if ($response instanceof BinaryFileResponse) {
            $content = $response->getFile()->getPathname();
            $type = self::RESPONSE_TYPE_FILE;

            return compact('statusCode', 'headers', 'content', 'type');
        }

        $content = $response->getContent();
        $type = self::RESPONSE_TYPE_NORMAL;

        return compact('statusCode', 'headers', 'content', 'type');
    }

    /**
     * Check whether the response properties is valid
     *
     * @param array $properties the response properties
     * 
     * @return boolean
     */
    protected function containsValidResponseProperties($properties): bool
    {
        if (! is_array($properties)) {
            return false;
        }

        if (! isset($properties['content'], $properties['statusCode'])) {
            return false;
        }

        return true;
    }

    /**
     * Build the response from the response properties
     *
     * @param array $responseProperties the response properties
     * 
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function buildResponse(array $responseProperties): Response
    {
        $type = $responseProperties['type'] ?? self::RESPONSE_TYPE_NORMAL;

        if ($type === self::RESPONSE_TYPE_FILE) {
            return new BinaryFileResponse(
                $responseProperties['content'],
                $responseProperties['statusCode']
            );
        }

        return new Response($responseProperties['content'], $responseProperties['statusCode']);
    }
}