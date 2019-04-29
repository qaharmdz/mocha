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

namespace Mocha\System;

mb_internal_encoding('UTF-8');
ini_set('display_errors', 0);

defined('MOCHA') or define('MOCHA', '1.0.0-a.1');

if (is_file(PATH_PUBLIC . '.maintenance')) {
    // Emergency maintenance
    exit('Maintenance | Don\'t worry it\'s not forever!');
}
if (version_compare($php = PHP_VERSION, $req = '7.1.8', '<')) {
    exit(sprintf('You are running PHP %s, Mocha require at least <b>PHP %s</b> to run.', $php, $req));
}
if (!is_file(PATH_PUBLIC . 'config.php')) {
    header('Location: setup/');
    exit;
}

// ====== Base

require_once PATH_MOCHA . '/system/vendor/' . DS . 'autoload.php';

// Protocols
$_https = false;
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' || !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
    $_https = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $_https = true;
}
$_SERVER['HTTPS'] = $_https;

// Configuration
$config = array_replace_recursive(
    require_once PATH_PUBLIC . 'config.php',    // public/config
    ['app' => $config_app]                      // public/index.php
);

$config['setting']['url_site'] = $config['setting']['url_site'];
$config['setting']['url_base'] = $config['setting']['url_site'] . $config['app']['url_part'];

// ====== Framework

$framework = new Framework();
$framework->init($config)->run();
