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

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpKernel;
use Symfony\Component\Routing;

class ProviderCore implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        // TODO: change . with _

        // === Request
        $container['request_stack'] = function ($c) {
            return new HttpFoundation\RequestStack();
        };
        $container['request'] = function ($c) {
            return Request::createFromGlobals();
        };

        // === Router
        $container['router_collection'] = function ($c) {
            return new Routing\RouteCollection();
        };
        $container['router_route'] = function ($c) {
            return function ($path, $defaults = [], $requirements = [], $options = [], $host = '', $schemes = [], $methods = [], $condition = '') {
                return new \Symfony\Component\Routing\Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
            };
        };
        $container['router_context'] = function ($c) {
            return new Routing\RequestContext();
        };
        $container['router_matcher'] = function ($c) {
            return new Routing\Matcher\UrlMatcher($c['router_collection'], $c['router_context']);
        };
        $container['router_generator'] = function ($c) {
            return new Routing\Generator\UrlGenerator($c['router_collection'], $c['router_context']);
        };
        $container['router'] = function ($c) {
            return new Router($c['router_collection'], $c['router_route'], $c['router_matcher'], $c['router_generator'], $c['parameterBag']);
        };

        // === Dispatcher
        $container['event'] = function ($c) {
            return new Event();
        };
        $container['resolver_controller'] = function ($c) {
            return new ResolverController($c['log'], $c['parameterBag']);
        };
        $container['resolver_argument'] = function ($c) {
            return new HttpKernel\Controller\ArgumentResolver();
        };
        $container['dispatcher'] = function ($c) {
            return new Dispatcher($c['event'], $c['resolver_controller'], $c['request_stack'], $c['resolver_argument']);
        };

        // Response
        $container['response'] = $container->factory(function ($c) {
            $response = new Response();
            $response->prepare($c['request']);

            return $response;
        });

        // Misc
        $container['session_option'] = [];
        $container['session_storage'] = function ($c) {
            return new HttpFoundation\Session\Storage\NativeSessionStorage($c['session_option']);
        };
        $container['session'] = function ($c) {
            return new Session($c['session_storage']);
        };

        $container['parameterBag'] = $container->factory(function ($c) {
            return new Config();
        });
        $container['config'] = function ($c) {
            return $c['parameterBag'];
        };

        $container['log_output'] = 'php://stderr';
        $container['log'] = function ($c) {
            return new HttpKernel\Log\Logger(\Psr\Log\LogLevel::DEBUG, $c['log_output']);
        };

        $container['presenter'] = function ($c) {
            return new Presenter($c['parameterBag']);
        };
    }
}
