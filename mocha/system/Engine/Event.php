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

namespace Mocha\System\Engine;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Simplify event dispatcher.
 */
class Event extends EventDispatcher
{
    /**
     * Event emitters storage.
     *
     * @var array
     */
    protected $emitters = [
        // For the sake of completeness
        'kernel.request',
        'kernel.controller',
        'kernel.controller_arguments',
        'kernel.view',
        'kernel.response',
        'kernel.terminate',
        'kernel.finish_request',
        'kernel.exception',
    ];

    /**
     * Trigger event to all registered listeners.
     *
     * @param  string $eventName
     * @param  array  $param
     * @param  mixed  $output
     *
     * @return mixed
     */
    public function trigger(string $eventName, array $param = [], $output = '')
    {
        $eventName = $this->parseEventName($eventName);

        return $this->dispatch(
            $eventName,
            new EventBag($eventName, $param, $output)
        );
    }

    /**
     * Get all emitter list.
     * Info: use "getListeners()" to get all registered listeners.
     *
     * @return array
     */
    public function getEmitters()
    {
        return $this->emitters;
    }

    /**
     * Format event name in dot notation.
     *
     * @param  string $name
     * @param  string $verb
     *
     * @return string
     */
    public function parseEventName(string $name)
    {
        $eventName = implode('.', array_unique(explode('/', strtolower($name))));

        if (!in_array($eventName, $this->emitters)) {
            $this->emitters[] = $eventName;
        }

        return $eventName;
    }
}
