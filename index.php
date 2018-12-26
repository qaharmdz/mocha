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

defined('ROOT') or define('ROOT', realpath(__DIR__) . DIRECTORY_SEPARATOR);

$config_app = [
    'folder'    => 'front',
    'url_part'  => '',
    'path'      => ROOT . 'front' . DIRECTORY_SEPARATOR,
    'namespace' => 'Mocha\Front',
];

require_once ROOT . 'system' . DIRECTORY_SEPARATOR . 'Startup.php';
