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

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('ROOT') or define('ROOT', __DIR__ . DS);

$config = require_once 'config.php';
$config['app'] = [
    'folder'    => 'front',
    'url_part'  => '',
    'path'      => ROOT . 'front' . DS,
    'namespace' => 'Mocha\Front',
];

require_once ROOT . 'system' . DS . 'Startup.php';
