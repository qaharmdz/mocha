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

if (!function_exists('sanitize_char')) {
    function sanitize_char($string, $glue = '-', $trim = '_-.')
    {
        // [1]<>+=_`~ !–@#$;"\'\%   ^&*(\{)?}/2=\ -,./../*:|3    result: 1-_-2-3
        return trim(preg_replace('/[\>\<\+\?\&\"\'\`\/\\\:\;\s\–\-\,\.\{\}\(\)\[\]\~\!\@\^\*\|\$\#\%\=\r\n\t]+/', $glue, $string), $trim);
    }
}
