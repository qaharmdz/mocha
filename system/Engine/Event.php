<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mocha\System\Engine;

use Symfony\Component\EventDispatcher\EventDispatcher;

class Event extends EventDispatcher
{
    /**
     * Shortcut for trigger action event
     *
     * @param  string $eventName
     * @param  array  $args
     */
    public function action(string $eventName, array $args = [])
    {
        $this->trigger($eventName, $args, 'action');
    }

    /**
     * Shortcut for trigger filter event
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
