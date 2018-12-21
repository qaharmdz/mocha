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

use Symfony\Component\EventDispatcher;

class EventBag extends EventDispatcher\Event
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $data;

    /**
     * @var array Readonly initial data passed to event
     */
    protected $defaultData;

    public function __construct(string $eventName, array $data = [])
    {
        $this->name = $eventName;
        $this->data = new Config($data);

        // All event have chance to access initial data
        if ($this->defaultData === null) {
            $this->defaultData = $this->data->all();
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
        return $this->defaultData;
    }

    /**
     * Get all changed data
     *
     * @return array
     */
    public function getAllData()
    {
        return $this->data->all();
    }

    /**
     * Special data key "_content"
     *
     * @return string
     */
    public function getContent()
    {
        return $this->data->get('_content');
    }
}
