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

namespace Mocha\System\Tool;

class Primary extends \Mocha\Controller
{
    /**
     * Load metadata file.
     * Info: $this in metadata is instance of this class.
     *
     * @param  string $extension
     * @param  string $name
     *
     * @return array
     */
    public function metafile(string $extension, string $name)
    {
        $data = [];
        $file = $this->config->get('system.path.' . $extension) . $name . DS . 'metadata.php';

        if (is_file($file)) {
            $data[$extension][$name] = include $file;
            $this->config->add(['meta' => $data]);

            return $data[$extension][$name];
        }

        return $data;
    }

    protected function load(string $path, array $args = [], string $extension = 'module')
    {
        $eventName = $eventName ?: $path;

        // Resolver
        $controller = $this->resolver_controller->resolve($path, $args);

        /**
         * Event to manipulate arguments.
         *
         * @return \Mocha\System\Engine\EventBag $arguments
         */
        $arguments = $this->event->trigger($eventName . '/before', $controller['arguments']);

        /**
         * Dispatch module to get response.
         *
         * @return \Mocha\System\Engine\Response $response
         */
        $response = call_user_func_array([new $controller['class'], $controller['method']], $arguments);


        /**
         * This event allows you to modify or replace the content that will be replied.
         */
        return $this->event->trigger($eventName . '/after', ['_response' => $response]);
    }

    /**
     * [render description]
     *
     * @param  string $template
     * @param  array  $vars
     * @param  string $eventName
     *
     * @return string
     */
    public function render(string $template, array $vars = [], string $eventName = '')
    {
        $eventName = ($eventName ?: $template) . '/render';

        // Event to manipulate twig variables
        $data = $this->event->trigger($eventName . '/before', $vars)->getData();

        // Event to manipulate render result
        return $this->event->trigger($eventName . '/after', [], $this->presenter->render($template, $data))->getOutput();
    }
}
