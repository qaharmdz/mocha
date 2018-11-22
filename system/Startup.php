<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mocha\System;

mb_internal_encoding('UTF-8');
ini_set('display_errors', 1);


// ====== Validate

if (version_compare($php = PHP_VERSION, $req = '7.1.8', '<')) {
    exit(sprintf('You are running PHP %s, Mocha require at least <b>PHP %s</b> to run.', $php, $req));
}
if (is_file(ROOT . '.maintenance')) {
    exit('<h1>Maintenance</h1><p>Website under maintenance. Please visit again later.</p>');
}
if (!is_file(ROOT . 'config.php')) {
    header('Location: install/');
    exit;
}


// ====== Base

require_once ROOT . '/system/vendor/' . DS . 'autoload.php';

// \Kint::$enabled_mode = false;

// Protocols
$_https = false;
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' || !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
    $_https = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $_https = true;
}
$_SERVER['HTTPS'] = $_https;
$_protocol = $_SERVER['HTTPS'] ? 'https://' : 'http://';

$config['setting']['url_site']    = rtrim($config['setting']['url_site'], '/.\\')  . '/';
$config['setting']['url_base']    = rtrim($config['setting']['url_site'] . $config['app']['url_part'], '/.\\')  . '/';


// ====== Framework

$framework = new Framework();
$framework->init($config);
$framework->run();

// d($framework);
// d($framework->config->all());
