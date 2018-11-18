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

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpKernel;
use Symfony\Component\Routing;

class ProviderCore implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        // === Request
        $container['request.stack'] = function ($c) {
            return new HttpFoundation\RequestStack();
        };
        $container['request'] = function ($c) {
            return Request::createFromGlobals();
        };

        // === Router
        $container['router.collection'] = function ($c) {
            return new Routing\RouteCollection();
        };
        $container['router.route'] = function ($c) {
            return function ($path, $defaults = [], $requirements = [], $options = [], $host = '', $schemes = [], $methods = [], $condition = '') {
                return new \Symfony\Component\Routing\Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
            };
        };
        $container['router.context'] = function ($c) {
            return new Routing\RequestContext();
        };
        $container['router.matcher'] = function ($c) {
            return new Routing\Matcher\UrlMatcher($c['router.collection'], $c['router.context']);
        };
        $container['router.generator'] = function ($c) {
            return new Routing\Generator\UrlGenerator($c['router.collection'], $c['router.context']);
        };
        $container['router'] = function ($c) {
            return new Router($c['router.collection'], $c['router.route'], $c['router.matcher'], $c['router.generator'], $c['paramBag']);
        };

        // === Dispatcher
        $container['event'] = function ($c) {
            return new Event();
        };
        $container['resolver.controller'] = function ($c) {
            return new ResolverController($c['log'], $c['paramBag']);
        };
        $container['resolver.argument'] = function ($c) {
            return new HttpKernel\Controller\ArgumentResolver();
        };
        $container['dispatcher'] = function ($c) {
            return new Dispatcher($c['event'], $c['resolver.controller'], $c['request.stack'], $c['resolver.argument']);
        };

        // Response
        $container['response'] = $container->factory(function ($c) {
            return new Response();
        });

        // Misc
        $container['session'] = function ($c) {
            return new Session();
        };

        $container['paramBag'] = $container->factory(function ($c) {
            return new Config();
        });
        $container['config'] = function ($c) {
            return $c['paramBag'];
        };

        $container['log.output'] = 'php://stderr';
        $container['log'] = function ($c) {
            return new HttpKernel\Log\Logger(\Psr\Log\LogLevel::DEBUG, $c['log.output']);
        };

        $container['presenter'] = function ($c) {
            return new Presenter($c['paramBag']);
        };
    }
}
