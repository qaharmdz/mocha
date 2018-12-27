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

// use Symfony\Component\HttpFoundation;
use Symfony\Component\EventDispatcher;

/**
 * General purpose EventDispatcher\Event
 */
class EventBag extends EventDispatcher\Event
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array Readonly initial data passed to event
     */
    protected $defaultParam;

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $data;

    /**
     * @var string
     */
    public $output;

    public function __construct(string $eventName, array $param = [], $output = '')
    {
        $this->name   = $eventName;
        $this->data   = new Config($param);
        $this->output = $output;

        // All event have chance to access initial data
        if ($this->defaultParam === null) {
            $this->defaultParam = array_merge(
                $this->data->all(),
                ['_output' => $this->output]
            );
        }
    }

    /**
     * Get triggered event name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get initial data
     *
     * @return array
     */
    public function getDefault()
    {
        return $this->defaultParam;
    }

    /**
     * Shortcut to update data
     *
     * @return array
     */
    public function editData(array $data)
    {
        $this->data->add($data);
    }

    /**
     * Shortcut to get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data->all();
    }

    /**
     * Get initial data
     *
     * @return array
     */
    public function getOutput()
    {
        return $this->output;
    }
}
