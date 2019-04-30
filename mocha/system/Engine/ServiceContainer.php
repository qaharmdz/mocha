<?php
/**
 * This file is part of Mocha.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Released under GPL version 3 or any later version.
 * Copyright and license see LICENSE file or https://www.gnu.org/licenses/gpl-3.0.en.html.
 */

namespace Mocha\System\Engine;

abstract class ServiceContainer
{
    private static $storage;

    /**
     * @param Container $storage
     * @param bool      $override
     */
    public static function storage(\ArrayAccess $storage)
    {
        if (self::$storage === null) {
            self::$storage = $storage;
        }
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
        return self::$storage[$identifier] ?? null;
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
}
