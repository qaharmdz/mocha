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

use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * An alias
 */
class Dispatcher extends HttpKernel
{
    public function __construct($event, $controllerResolver, $requestStack, $argumentResolver)
    {
        parent::__construct($event, $controllerResolver, $requestStack, $argumentResolver);
    }
}
