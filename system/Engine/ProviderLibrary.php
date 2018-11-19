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
use Pimple\ServiceProviderInterface;

class ProviderLibrary implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['database.param'] = [];
        $container['database'] = function ($c) {
            $db = new \Mysqlidb($c['database.param']);
            $db->rawQuery('SET session group_concat_max_len = 102400;');
            $db->rawQuery('SET SESSION sql_mode="STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION";');

            try {
                $db->mysqli();
            } catch (Exception $e) {
                throw new \InvalidArgumentException('Could not connect to database.');
            }

            return \MysqliDb::getInstance();
        };
    }
}
