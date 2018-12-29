<?php
/*
 * This file is part of Mocha package.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Released under GPL version 3 or any later version.
 * Full copyright and license see LICENSE file or visit https://www.gnu.org/licenses/gpl-3.0.en.html.
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
            'tool' => 'Mocha\System\Tool\Primary',
        ];

        foreach ($tools as $key => $class) {
            $container[$key] = function ($c) use ($class) {
                return new $class();
            };
        }
    }
}
