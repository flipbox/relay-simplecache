<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipbox/craft-simplecache/blob/master/LICENSE
 * @link       https://github.com/flipbox/craft-simplecache
 */

namespace Flipbox\Relay\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ClearSimpleCache extends AbstractSimpleCache
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

        $response = $next($request, $response);

        // Only cache successful responses
        if ($this->isResponseSuccessful($response)) {
            $key = $this->getCacheKey($request);

            if ($this->cache->delete($key)) {
                $this->info(
                    "Item removed from cache successfully. [key: {key}, type: {type}]",
                    [
                        'type' => get_class($this->cache),
                        'key' => $key
                    ]
                );
            } else {
                $this->info(
                    "Item not removed from cache. [key: {key}, type: {type}]",
                    [
                        'type' => get_class($this->cache),
                        'key' => $key
                    ]
                );
            }
        }
        return $response;
    }
}
