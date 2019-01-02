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

require_once '../path.php';

$config_app = [
    'folder'     => 'admin',
    'url_part'   => 'admin',
    'path'       => PATH_MOCHA . 'admin' . DS,
    'namespace'  => $namespace = 'Mocha\Admin',
    'controller' => $namespace . '\Component\Login::index'
];

require_once PATH_MOCHA . 'system' . DS . 'Startup.php';
