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
