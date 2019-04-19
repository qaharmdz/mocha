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

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;

/**
 * Opiniated controller resolver.
 */
class ResolverController extends ControllerResolver
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $param;

    public function __construct(LoggerInterface $logger, ParameterBag $bag)
    {
        parent::__construct($logger);

        $this->log   = $logger;
        $this->param = $bag;

        $this->param->add([
            'namespace'     => [
                'component' => 'Component',
                'module'    => 'Module',
                'plugin'    => 'Plugin',
                'theme'     => 'Theme'
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @param  Request  $request
     *
     * @return callable|false A PHP callable representing the Controller,
     *                        or false if this resolver is not able to determine the controller.
     */
    public function getController(Request $request)
    {
        if (!is_callable($request->attributes->get('_controller'))) {
            if (false !== $controller = $this->resolve($request->attributes->get('_controller'), $request->attributes->get('_route_params'))) {
                $request->attributes->set('_controller', [new $controller['class'], $controller['method']]);
                $request->query->set('route', $controller['path']);
                $request->query->add($controller['arguments']);
            } else {
                return false;
            }
        }

        return parent::getController($request);
    }

    /**
     * Responsible to get controller from given path.
     *
     * @param  string $path
     * @param  array  $params
     * @param  string $extension
     *
     * @return array|false
     */
    public function resolve(string $path, array $params = [], string $extension = 'component')
    {
        $namespace = $this->param->get('namespace.' . $extension);
        $segments  = explode('/', trim($path, '/'));

        if (empty($segments[0])) {
            throw new \InvalidArgumentException(sprintf('Unable to resolve path "%s"', $path));
        }

        try {
            list($class, $path) = $this->resolveClass($path, $namespace, $segments);
            $method    = $this->resolveMethod($class, $segments);
            $arguments = $this->resolveArguments($params, $segments);
            $path      = strtolower($path . ($method != 'index' ?  '/' . $method : ''));

            return [
                'path'      => $path,
                'class'     => $class,
                'method'    => $method,
                'arguments' => $arguments
            ];
        } catch (\Exception $e) {
            $this->log->critical($e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());

            return false;
        }
    }

    /**
     * @param  string $path
     * @param  string $namespace
     * @param  array  &$segments
     *
     * @return string
     */
    protected function resolveClass($path, $namespace, &$segments)
    {
        $folder  = $classname = ucwords(array_shift($segments));
        $parts   = $_parts = [rtrim($namespace, '\\'), $folder, 'Controller', $classname];
        $segment = !empty($segments[0]) ? $segments[0] : '';

        if ($segment) {
            $_parts[3] = ucwords($segment);
            $classname = $_parts[3];
        }

        $namespace = implode('\\', $_parts);

        if (class_exists($namespace)) {
            array_shift($segments);
        } else {
            $namespace = implode('\\', $parts);
            $classname = $parts[3];

            if (!class_exists($namespace)) {
                throw new \InvalidArgumentException(sprintf('Cannot locate expected controller "%s" for route path "%s"', $classname, $path));
            }
        }

        return [
            $namespace,
            $folder != $classname ? $folder . '/' . $classname : $folder
        ];
    }

    /**
     * @param  array  &$segments
     *
     * @return string
     */
    protected function resolveMethod($class, &$segments)
    {
        $method    = 'index';
        $blacklist = ['setStorage'];

        if (!empty($segments[0])
          && !in_array($segments[0], $blacklist)
          && !is_numeric($segments[0][0])
          && strncmp($segments[0], '__', 2) !== 0
          && is_callable([$class, $segments[0]])) {
            $method = array_shift($segments);
        }

        return $method;
    }

    /**
     * @param  array  $params
     * @param  array  &$segments
     *
     * @return string
     */
    protected function resolveArguments($params, &$segments)
    {

        if (!empty($segments[0])) {
            $_params = [];
            foreach (array_chunk($segments, 2) as $pair) {
                $_params[$pair[0]] = $pair[1] ?? '';
            }

            $params = array_replace($_params, $params);
        }

        return $params;
    }
}
