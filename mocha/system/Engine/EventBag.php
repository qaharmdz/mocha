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

use Symfony\Component\EventDispatcher;

/**
 * General purpose EventBag.
 */
class EventBag extends EventDispatcher\Event
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array Read only initial data passed to event.
     */
    protected $default;

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $data;

    /**
     * @var string
     */
    public $output;

    /**
     * Single event emitter can have multiple listeners
     * Property $data and $output might be used or modificated by all listeners
     * While $default always contain the original data
     *
     * @param string $eventName
     * @param array  $param
     * @param string $output
     */
    public function __construct(string $eventName, array $param = [], $output = '')
    {
        $this->name   = $eventName;
        $this->data   = new Config($param);
        $this->output = $output;

        // All event have chance to access initial data
        if ($this->default === null) {
            $this->default = array_merge(
                $this->data->all(),
                ['_output' => $this->output]
            );
        }
    }

    /**
     * Get triggered event name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get initial data.
     *
     * @return array
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Shortcut to get data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data->all();
    }

    /**
     * Get output.
     *
     * @return array
     */
    public function getOutput()
    {
        return $this->output;
    }
}
