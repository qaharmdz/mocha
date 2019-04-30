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
}
