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
     * Shortcut for dispatchHook action
     *
     * @param  string $eventName
     * @param  array  $args
     */
    public function action(string $eventName, array $args = [])
    {
        $this->dispatchHook($eventName, $args, 'action');
    }

    /**
     * Shortcut for dispatchHook filter
     *
     * @param  string $eventName
     * @param  array  $args
     *
     * @return array
     */
    public function filter(string $eventName, array $args = [])
    {
        return $this->dispatchHook($eventName, $args, 'filter');
    }

    /**
     * Dispatch event \Gubug\Event\Hook to all registered listeners
     *
     * @param  string $eventName
     * @param  array  $args
     * @param  string $type
     *
     * @return \Gubug\Event\Hook
     */
    public function dispatchHook(string $eventName, array $args = [], string $type = 'action')
    {
        return $this->dispatch(
            $type . '.' . $eventName,
            new EventBag($eventName, $args)
        );
    }
}
