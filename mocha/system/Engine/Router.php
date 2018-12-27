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

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Wrap Symfony routing in one class
 */
class Router
{
    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    public $collection;

    /**
     * @var callable \Symfony\Component\Routing\Route
     */
    public $route;

    /**
     * @var \Symfony\Component\Routing\Matcher\UrlMatcher
     */
    public $urlMatcher;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGenerator
     */
    public $urlGenerator;

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $param;

    public function __construct(RouteCollection $collection, $route, UrlMatcher $urlMatcher, UrlGenerator $urlGenerator, ParameterBag $bag)
    {
        $this->collection   = $collection;
        $this->route        = $route;
        $this->urlMatcher   = $urlMatcher;
        $this->urlGenerator = $urlGenerator;
        $this->param        = $bag;

        $this->param->add([
            'routeDefaults'     => ['_locale' => 'en'], // Default addRoute
            'routeRequirements' => ['_locale' => 'en'], // Requirement addRoute, multi-language en|id|fr
            'buildFlatten'      => false,               // Implode query url
            'buildLocale'       => false,               // Force urlBuild to use "_locale"
            'buildParameters'   => []                   // Force urlBuild to add extra parameter
        ]);
    }

    /**
     * Add route into collection.
     *
     * @param string          $name         Route name
     * @param string          $path         The path pattern to match
     * @param array           $defaults     An array of default parameter values
     * @param array           $requirements An array of requirements for parameters (regexes)
     * @param string|string[] $methods      A required HTTP method or an array of restricted methods
     * @param array           $options      An array of options
     * @param string          $host         The host pattern to match
     * @param string|string[] $schemes      A required URI scheme or an array of restricted schemes
     * @param string          $condition    A condition that should evaluate to true for the route to match
     */
    public function addRoute(string $name, string $path, array $defaults = [], array $requirements = [], $methods = [], array $options = ['utf8' => true], ?string $host = '', $schemes = [], ?string $condition = '')
    {
        $route = $this->newRoute(
            $path,
            array_replace($defaults, $this->param->get('routeDefaults')),
            array_replace($requirements, $this->param->get('routeRequirements')),
            $options,
            $host,
            $schemes,
            $methods,
            $condition
        );

        $this->collection->add($name, $route);
    }

    /**
     * Helper on using router route to add collection
     *
     * @param  mixed $params
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function newRoute(...$params)
    {
        return call_user_func($this->route, ...$params);
    }

    /**
     * Generate url by route name
     *
     * @param  string $name       Route name
     * @param  array  $params     Route parameter
     * @param  bool   $extraParam Should extra parameter appended, mostly url token
     *
     * @return string
     */
    public function urlBuild(string $name, array $params = [], bool $extraParam = true)
    {
        $result = '';

        // Check to avoid exception error
        if ($this->collection->get($name)) {
            $name = $this->param->get('buildLocale') ? preg_replace('/_locale$/', '', $name) . '_locale' : $name;
            $params = $extraParam ? array_replace($params, $this->param->get('buildParameters')) : $params;

            $result = $this->urlGenerator->generate($name, $params, UrlGenerator::ABSOLUTE_URL);
        }

        // Flatten
        if ($this->param->get('buildFlatten') && $result) {
            $parts = explode('?', $result);

            if (!empty($parts[1])) {
                $result = rtrim($parts[0], '/') . '/' . implode('/', explode('=', str_replace('&', '=', $parts[1])));
            }
        }

        // TODO: plugin event to use url alias from database
        // TODO: for reverse, use event "init.start" to manipulate request->pathinfo

        return $result;
    }

    /**
     * Helper to automatically check route $name in urlBuild
     *
     * @param  string $path       Route path
     * @param  array  $params     Route parameter
     * @param  bool   $extraParam Append extra parameter?
     *
     * @return string
     */
    public function urlGenerate(string $path = '', array $params = [], bool $extraParam = true)
    {
        if (!$path) {
            return $this->urlBuild('_base', $params, $extraParam);
        }
        if ($this->collection->get($path)) {
            return $this->urlBuild($path, $params, $extraParam);
        }

        $params['_controller'] = $path;

        return $this->urlBuild('_dynamic', $params, $extraParam);
    }
}
