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

class Controller extends Abstractor
{
    /**
     * [render description]
     *
     * @param  string $template
     * @param  array  $vars
     * @param  string $eventName
     *
     * @return string
     */
    protected function render(string $template, array $vars = [], string $eventName = '')
    {
        $eventName = $eventName ?: $template . '/presenter';

        // Event to manipulate twig variables
        $data = $this->event->trigger($eventName . '_data', $vars)->getData();

        // Event to manipulate render result
        return $this->event->trigger($eventName . '_output', [], $this->presenter->render($template, $data))->getOutput();
    }
}
