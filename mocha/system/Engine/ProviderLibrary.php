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

namespace Mocha\System\Engine;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Mocha\System\Library;

class ProviderLibrary implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['database_param'] = [];
        $container['database'] = function ($c) {
            try {
                $db = new \Mysqlidb($c['database_param']);
                $db->rawQuery('SET session group_concat_max_len = 102400;');
                $db->rawQuery('SET SESSION sql_mode="STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION";');
                $db->mysqli();
            } catch (Exception $e) {
                throw new \RuntimeException();
                exit('Can\'t get motivated without CAFFEINE |-_-|');
            }

            return \MysqliDb::getInstance();
        };

        $container['secure'] = function ($c) {
            return new Library\Secure($c['parameterBag']);
        };

        $container['user'] = function ($c) {
            return new Library\User($c['database'], $c['secure'], $c['session']);
        };

        $container['language'] = function ($c) {
            return new Library\Language($c['parameterBag']);
        };

        $container['document'] = function ($c) {
            return new Library\Document();
        };

        $container['date_carbon'] = function ($c) {
            return new \Carbon\Carbon();
        };
        $container['date'] = function ($c) {
            return new Library\Date($c['date_carbon'], $c['parameterBag']);
        };
    }
}
