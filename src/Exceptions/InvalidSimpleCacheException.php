<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipbox/relay-simplecache/blob/master/LICENSE
 * @link       https://github.com/flipbox/relay-simplecache
 */

namespace Flipbox\Relay\Exceptions;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class InvalidSimpleCacheException extends \Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Invalid Simple Cache';
    }
}
