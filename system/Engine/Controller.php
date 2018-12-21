<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mocha;

class Controller extends System\Engine\ServiceContainer
{
    /**
     * Fallback inaccessible properties to ServiceContainer
     *
     * @param  string $service
     *
     * @return object
     */
    public function __get(string $service)
    {
        return $this->use($service);
    }
}
