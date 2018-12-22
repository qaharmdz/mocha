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

namespace Mocha\System\Engine;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Inspired by WordPress hook: action and filter
 */
class Event extends EventDispatcher
{
    /**
     * Trigger a response/ reaction of an activity
     *
     * @param  string $eventName
     * @param  array  $args
     */
    public function action(string $eventName, array $args = [])
    {
        $this->trigger($eventName, $args, 'action');
    }

    /**
     * An action to modify activity data
     *
     * @param  string $eventName
     * @param  array  $args
     *
     * @return array
     */
    public function filter(string $eventName, array $args = [])
    {
        return $this->trigger($eventName, $args, 'filter');
    }

    /**
     * Trigger event to all registered listeners
     *
     * @param  string $eventName
     * @param  array  $args
     * @param  string $type
     *
     * @return Mocha\System\Engine\EventBag
     */
    public function trigger(string $eventName, array $args = [], string $type = 'action')
    {
        return $this->dispatch(
            $type . '.' . $eventName,
            new EventBag($eventName, $args)
        );
    }
}
