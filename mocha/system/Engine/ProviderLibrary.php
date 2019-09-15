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

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Mocha\System\Library;

class ProviderLibrary implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['database_param'] = [];
        $container['db'] = function ($c) {
            $dsn = $c['database_param']['driver'] . ':host=' . $c['database_param']['host'] . ';dbname=' . $c['database_param']['db'] . ';charset=' . $c['database_param']['charset'];
            try {
                $pdo = new Library\Database($dsn, $c['database_param']['username'], $c['database_param']['password'], $c['database_param']['options']);
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
                exit('Can\'t get motivated without Caffeine');
            }

            return $pdo;
        };

        $container['secure'] = function ($c) {
            return new Library\Secure($c['parameterBag']);
        };

        $container['user'] = function ($c) {
            return new Library\User($c['db'], $c['secure'], $c['session']);
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

        $container['valid'] = $container->factory(function ($c) {
            return new \Respect\Validation\Validator();
        });
    }
}
