<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mocha\System\Engine;

use Pimple\Container;

abstract class ServiceContainer
{
    private static $storage;

    /**
     * @param Container $storage
     * @param bool      $override
     */
    public static function storage(Container $storage)
    {
        if (self::$storage === null) {
            self::$storage = $storage;
        }
    }

    /**
     * Full access to container
     *
     * @return \Pimple\Container
     */
    protected function container()
    {
        return self::$storage;
    }

    /**
     * Access a service
     *
     * @param  string $identifier
     *
     * @return mixed
     */
    protected function use(string $identifier)
    {
        return self::$storage[$identifier];
    }
}
