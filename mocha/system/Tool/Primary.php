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

namespace Mocha\System\Tool;

use Mocha\Controller;
use Mocha\Abstractor;

class Primary extends Controller
{
    /**
     * @var Mocha\System\Engine\Config
     */
    protected $bags;
    // protected $data = [];

    public function __construct()
    {
        $this->bags = $this->parameterBag;
    }

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

    /**
     * Load and wrap controller with before/after event
     *
     * @param  string $path
     * @param  array  $args
     * @param  string $extension
     * @param  string $eventName
     *
     * @return mixed
     */
    public function controller(string $path, array $args = [], string $extension = 'component', string $eventName = '')
    {
        $eventName = $eventName ?: $extension . '.' . $path;

        // Resolver
        $controller = $this->resolver_controller->resolve($path, $args, $extension);

        /**
         * Event to manipulate arguments.
         *
         * @return array
         */
        $arguments = $this->event->trigger($eventName . '.before', $controller['arguments'])->getData();

        /**
         * Dispatch to get response.
         *
         * @return \Mocha\System\Engine\Response $response
         */
        $response = call_user_func_array([new $controller['class'], $controller['method']], $arguments);

        /**
         * This event allows you to modify or replace the returned data.
         */
        if (is_array($response)) {
            return $this->event->trigger($eventName . '.after', $response)->getData();
        } else {
            return $this->event->trigger($eventName . '.after', [], $response)->getOutput();
        }
    }

    /**
     * Wrap abstractor with before/after event
     *
     * @param  string           $path
     * @param  Abstractor|array $param
     *
     * @return mixed
     */
    /**
     * TODO:
     * $this->tool->abstractor('system\setting', new Component\System\Abstractor\Setting());
     * $this->tool->abstractor('system\setting')->method('getSettings', ['setting', $page])
     */
    public function abstractor(string $path, $param = [])
    {
        if ($param instanceof Abstractor) {
            if (!$this->bags->has('abstractor.' . $path)) {
                $this->bags->set('abstractor.' . $path, $param);
            }
        } else {
            $abstractor = explode('.', $path);

            if ($this->bags->has('abstractor.' . $abstractor[0])) {
                // TODO: wrap in event .before
                return $this->bags->get('abstractor.' . $abstractor[0])->{$abstractor[1]}(...$param);
                // TODO: wrap in event .after
            }

            $this->logger->error(sprintf('Cannot locate abstractor path "%s"!', $path));
            return false;
        }
    }

    /**
     * Wrap presenter render with before/after event
     *
     * @param  string $template
     * @param  array  $vars
     * @param  string $eventName
     *
     * @return string
     */
    public function render(string $template, array $vars = [], string $eventName = '')
    {
        $eventName = ($eventName ?: $template) . '.render';
        $vars['_template'] = $template;

        /**
         * Event to manipulate twig variables.
         *
         * @return array
         */
        $data = $this->event->trigger($eventName . '.before', $vars)->getData();

        /**
         * Event to manipulate render result.
         */
        return $this->event->trigger($eventName . '.after', [], $this->presenter->render($data['_template'], $data))->getOutput();
    }

    public function compress($response)
    {
        $encodings = $this->request->getEncodings();

        if (in_array('gzip', $encodings) && function_exists('gzencode')) {
            $content = gzencode($response->getContent());
            $response->setContent($content);
            $response->headers->set('Content-encoding', 'gzip');
        } elseif (in_array('deflate', $encodings) && function_exists('gzdeflate')) {
            $content = gzdeflate($response->getContent());
            $response->setContent($content);
            $response->headers->set('Content-encoding', 'deflate');
        }

        return $response;
    }

    /**
     * Helper to throw error; catch by $.ajaxError at theme.js
     *
     * @param  string  $message
     * @param  int     $status
     *
     * @return \Mocha\System\Engine\Response
     */
    public function errorAjax(string $message, int $status)
    {
        return $this->response->jsonOutput(['message' => $message], $status);
    }

    /**
     * Helper to throw "view" permission issue
     *
     * @param  string $message
     *
     * @return \Mocha\System\Engine\Response
     */
    public function errorPermission(string $message = '')
    {
        $this->document->setTitle('403 Forbidden!');
        $this->document->addNode('class_body', ['path-error status-403']);

        return $this->response
            ->setStatusCode(403)
            ->setContent($this->tool->render('error', [
                'title'    => '403 Forbidden!',
                'subtitle' => $message ?: 'You not have permission to access!',
                'content'  => ''
            ]));
    }
}
