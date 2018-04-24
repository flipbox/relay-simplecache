<?php
/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipbox/relay-simplecache/blob/master/LICENSE
 * @link       https://github.com/flipbox/relay-simplecache
 */

namespace Flipbox\Relay\Middleware;

use Flipbox\Http\Stream\Factory as StreamFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SimpleCache extends AbstractSimpleCache
{
    /**
     * @inheritdoc
     */
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ): ResponseInterface {
        parent::__invoke($request, $response);

        $key = $this->getCacheKey($request);

        $value = $this->cache->get($key);

        // If it's cached
        if ($value !== null) {
            return $this->applyCacheToResponseBody($key, $response, $value);
        }

        $this->info(
            "Item not found in cache. [key: {key}, type: {type}]",
            [
                'type' => get_class($this->cache),
                'key' => $key
            ]
        );

        /** @var ResponseInterface $response */
        $response = $next($request, $response);

        // Only cache successful responses
        if ($this->isResponseSuccessful($response)) {
            $this->cacheResponse($key, $response);
        } else {
            $this->info(
                "Did not save to cache because request was unsuccessful. [key: {key}, statusCode: {statusCode}]",
                [
                    'key' => $key,
                    'statusCode' => $response->getStatusCode()
                ]
            );
        }
        return $response;
    }

    /**
     * @param string $key
     * @param ResponseInterface $response
     * @param mixed $value
     * @return ResponseInterface
     * @throws \Flipbox\Http\Stream\Exceptions\InvalidStreamException
     */
    protected function applyCacheToResponseBody(string $key, ResponseInterface $response, $value)
    {
        $this->info(
            "Item found in cache. [key: {key}, type: {type}]",
            [
                'type' => get_class($this->cache),
                'key' => $key
            ]
        );

        return $response->withBody(
            StreamFactory::create($value)
        );
    }

    /**
     * @param string $key
     * @param ResponseInterface $response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function cacheResponse(string $key, ResponseInterface $response)
    {
        /** @var StreamInterface $body */
        $body = $response->getBody();

        $this->cache->set($key, $body->getContents());

        $body->rewind();

        $this->info(
            "Save item to cache. [key: {key}, type: {type}]",
            [
                'type' => get_class($this->cache),
                'key' => $key
            ]
        );
    }
}
