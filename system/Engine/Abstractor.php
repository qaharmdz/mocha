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

class Abstractor extends System\Engine\ServiceContainer
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
     * Info: can access $this inside metadata
     *
     * @param  string $extension
     * @param  string $name
     *
     * @return array
     */
    protected function meta(string $extension, string $name)
    {
        $data = [];
        $file = $this->config->get('system.path.' . $extension) . $name . DS . 'metadata.php';

        if (is_file($file)) {
            $data[$extension][$name] = include $file;
            $this->config->add($data);

            return $data[$extension][$name];
        }

        return $data;
    }
}
