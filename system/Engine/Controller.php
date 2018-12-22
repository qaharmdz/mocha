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

namespace Mocha;

class Controller extends System\Engine\ServiceContainer
{
    /**
     * Fallback inaccessible properties to ServiceContainer
     *
     * @param  string $service
     *
     * @return object
     */
    public function __get(string $service)
    {
        return $this->use($service);
    }

    /**
     * Load metadata file
     *
     * @param  string $extension
     * @param  string $name
     *
     * @return array
     */
    public function meta(string $extension, string $name)
    {
        $file = $this->config->get('system.path.' . $extension) . $name . DS . 'metadata.php';

        if (is_file($file)) {
            return include $file;
        }

        return [];
    }
}
