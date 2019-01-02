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

namespace Mocha\System\Tool;

class Secure extends \Mocha\Controller
{
    /**
     * Remove unwanted characters.
     * Example: [1]<>+=_`~ !–@#$;"\'\%   ^&*(\{)?}/2=\ -,./../*:|3
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
                $data[$key] = html_escape($value);
            }

            return $data;
        }

        return htmlspecialchars($data, ENT_QUOTES, $charset);
    }

    /**
     * An alias to generate 'random' code.
     *
     * @param  string      $type
     * @param  int|integer $length
     *
     * @return string
     */
    public function generateCode(string $type = 'alnum', int $length = 16)
    {
        return $this->secure->generateCode($type, $length);
    }

    public function csrfToken()
    {
        return $this->user->fingerprint();
    }

    /**
     * Generate csrf form input.
     *
     * @return string
     */
    public function csrfField()
    {
        return '<input type="hidden" name="csrf-token" value="' . $this->csrfToken() . '" class="csrf-token" />';
    }

    /**
     * Validate post csrf form input.
     *
     * @return bool
     */
    public function csrfValidate()
    {
        return $this->request->post->get('csrf-token') === $this->csrfToken();
    }
}
