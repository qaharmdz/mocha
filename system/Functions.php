<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('sanitize_char')) {
    function sanitize_char($string, $glue='-', $trim='_-.') {
        // [1]<>+=_`~ !–@#$;"\'\%   ^&*(\{)?}/2=\ -,./../*:|3    result: 1-_-2-3
        return trim(preg_replace('/[\>\<\+\?\&\"\'\`\/\\\:\;\s\–\-\,\.\{\}\(\)\[\]\~\!\@\^\*\|\$\#\%\=\r\n\t]+/', $glue, $string), $trim);
    }
}
