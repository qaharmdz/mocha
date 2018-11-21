<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mocha\System\Tool;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Mocha\System\Tool;

class ProviderTool implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $tools = [
            'tool_taxonomy' => 'Tool\Taxonomy',
        ];

        foreach ($tools as $key => $class) {
            $container[$key] = function ($c) {
                return new $class();
            };
        }
    }
}
