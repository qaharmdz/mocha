<?php
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('ROOT') or define('ROOT', __DIR__ . DS);

$config = require_once 'config.php';
$config['app'] = [
    'folder'   => 'front',
    'url_part' => '',
];

require_once ROOT . 'system' . DS . 'Startup.php';
