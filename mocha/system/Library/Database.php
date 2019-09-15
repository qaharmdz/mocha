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

namespace Mocha\System\Library;

/**
 * Simple yet efficient PDO wrapper
 *
 * @see     https://phpdelusions.net/pdo/pdo_wrapper#Extending
 */
class Database extends \PDO
{
    public function __construct($dsn, $username = null, $password = null, $options = [])
    {
        $default_options = [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => '
                SET time_zone="+00:00",
                    SESSION group_concat_max_len = 102400,
                    SESSION sql_mode="STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
            ',
        ];
        $options = array_replace($default_options, $options);

        parent::__construct($dsn, $username, $password, $options);
    }

    public function run($sql, $args = null)
    {
        if (!$args) {
            return $this->query($sql);
        }

        $stmt = $this->prepare($sql);
        $stmt->execute($args);

        return $stmt;
    }
}
