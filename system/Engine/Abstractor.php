<?php
/*
 * This file is part of Mocha package.
 *
 * This program is a "free software" which mean freedom to use, modify and redistribute.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Released under GPL version 3 or any later version.
 * Full copyright and license see LICENSE file or visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 */

namespace Mocha;

class Abstractor extends System\Engine\ServiceContainer
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

    // Wrap method access with event
    public function __call($method, $arguments)
    {
        if (method_exists($this, $method)) {
            $this->test1();
            $response = call_user_func_array([$this, $method], $arguments);
            $this->test2();

            return $response;
        }
    }
}
