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

require_once 'path.php';

$config_app = [
    'folder'    => 'front',
    'url_part'  => '',
    'path'      => PATH_MOCHA . 'front' . DS,
    'namespace' => 'Mocha\Front'
];

require_once PATH_MOCHA . 'system' . DS . 'Startup.php';
