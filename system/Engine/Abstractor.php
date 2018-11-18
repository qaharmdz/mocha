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
    public function __get(string $service)
    {
        return $this->use($service);
    }
}
