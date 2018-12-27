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
defined('PATH_ROOT') or define('PATH_ROOT', realpath(__DIR__ . './../') . DS);
defined('PATH_MOCHA') or define('PATH_MOCHA', realpath(PATH_ROOT . './mocha/') . DS);
defined('PATH_PUBLIC') or define('PATH_PUBLIC', realpath(__DIR__) . DS);

// TODO: (1) Keep here or remove to plugin?
$symlinks = [
    realpath(PATH_MOCHA . './storage/image/') => PATH_PUBLIC . 'image' . DS,
    realpath(PATH_MOCHA . './front/theme/base/') => PATH_PUBLIC . 'asset' . DS . 'theme_base'
];
foreach ($symlinks as $real => $link) {
    if (file_exists($real) && (!is_link($link) || readlink($link) != $real)) {
        try {
            if (file_exists($link)) {
                @unlink($link);
            }

            if (!file_exists($link)) {
                symlink($real, $link);
            }
        } catch (\Exception $e) {
            exit('DEPRESSO | The feeling you get when you run out of coffee!');
        }
    }
}
