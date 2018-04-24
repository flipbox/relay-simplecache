<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipbox/relay-simplecache/blob/master/LICENSE
 * @link       https://github.com/flipbox/relay-simplecache
 */

namespace Flipbox\Relay\Middleware;

use Flipbox\Relay\Exceptions\InvalidSimpleCacheException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 */
abstract class AbstractSimpleCache extends AbstractMiddleware
{
    /**
     * @var CacheInterface
     */
    public $cache;

    /**
     * @var string
     */
    public $key;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!$this->cache instanceof CacheInterface) {
            throw new InvalidSimpleCacheException(
                sprintf(
                    "The class '%s' requires a cache instance of '%s', '%s' given.",
                    get_class($this),
                    CacheInterface::class,
                    get_class($this->cache)
                )
            );
        }
    }

    /**
     * Returns the id used to cache a request.
     *
     * @param RequestInterface $request
     *
     * @return string
     */
    protected function getCacheKey(RequestInterface $request): string
    {
        if ($this->key === null) {
            $this->key = $request->getMethod() . md5((string)$request->getUri());
        }
        return (string)$this->key;
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    protected function isResponseSuccessful(ResponseInterface $response): bool
    {
        if ($response->getStatusCode() >= 200 &&
            $response->getStatusCode() < 300
        ) {
            return true;
        }
        $this->warning(
            "API request was not successful",
            [
                'code' => $response->getStatusCode(),
                'reason' => $response->getReasonPhrase()
            ]
        );
        return false;
    }
}
