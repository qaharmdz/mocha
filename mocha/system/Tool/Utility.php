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

namespace Mocha\System\Tool;

class Utility extends \Mocha\Controller
{
    /**
     * Remove unwanted characters for filename,  url alias etc
     * Example: [1]<>+=_`~ !–@#$;"\'\%   ^&*(\{)?}/2=\ -,./../*:|3_-.#
     * Result: 1-_-2-3
     *
     * @param  string $data
     * @param  string $glue
     * @param  string $trim
     *
     * @return string
     */
    public function sanitizeChar(string $data, $glue = '-', $trim = '_-.')
    {
        return trim(preg_replace('/[\>\<\+\?\&\"\'\`\/\\\:\;\s\–\-\,\.\{\}\(\)\[\]\~\!\@\^\*\|\$\#\%\=\r\n\t]+/', $glue, $data), $trim);
    }

    /**
     * Convert special characters to HTML entities.
     *
     * @param  string|array $data
     * @param  string       $charset
     *
     * @return mixed
     */
    public function htmlEscape($data, $charset = 'UTF-8')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = htmlEscape($value);
            }

            return $data;
        }

        return htmlspecialchars($data, ENT_QUOTES, $charset);
    }

    /**
     * Format bytes to readable unit
     *
     * @link   https://stackoverflow.com/a/2510459
     *
     * @param  integer $bytes
     * @param  integer $precision
     *
     * @return string
     */
    public function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);

        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
