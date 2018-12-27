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

defined('MOCHA') or define('MOCHA', '1.0.0-a.1');

mb_internal_encoding('UTF-8');
ini_set('display_errors', 1);

// ====== Validate

if (version_compare($php = PHP_VERSION, $req = '7.1.8', '<')) {
    exit(sprintf('You are running PHP %s, Mocha require at least <b>PHP %s</b> to run.', $php, $req));
}
if (is_file(PATH_PUBLIC . '.maintenance')) {
    exit('<h1>Maintenance</h1><p>Website under maintenance. Please visit again later.</p>');
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
    require_once PATH_PUBLIC . 'config.php',
    ['app' => $config_app]
);

$config['setting']['url_site'] = $config['setting']['url_site'];
$config['setting']['url_base'] = $config['setting']['url_site'] . $config['app']['url_part'];

// TODO: (1) Symbolic links check to plugin
// $symlinks = [
//     realpath(PATH_MOCHA . './storage/image/') => PATH_PUBLIC . 'image' . DS
// ];
// foreach ($symlinks as $real => $link) {
//     if (file_exists($real) && (!is_link($link) || readlink($link) != $real)) {
//         try {
//             if (file_exists($link)) {
//                 @unlink($link);
//             }

//             if (!file_exists($link)) {
//                 symlink($real, $link);

//                 !d(
//                     file_exists($link),
//                     is_link($link),
//                     readlink($link),
//                     linkinfo($link)
//                 );
//             }
//         } catch (\Exception $e) {
//             exit('Symbolic link issues!');
//         }
//     }
// }

// ====== Framework

$framework = new Framework();
$framework->init($config)->run();
