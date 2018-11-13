<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mocha\System;

use Symfony\Component\Debug;
use Symfony\Component\HttpKernel\EventListener;

class Framework
{
    /**
     * @var \Pimple\Container
     */
    public $container;

    /**
     * @var \Mocha\System\Engine\Config
     */
    public $config;

    public function __construct()
    {
        $this->container = new \Pimple\Container;
        $this->container->register(new Engine\ProviderCore());
        $this->container->register(new Engine\ProviderLibrary());
    }

    /**
     * @param  array  $config
     */
    public function init(array $config = [])
    {
        $this->initConfig($config);
        $this->initService();
        $this->initSession();
        $this->initEvent();
        $this->initRouter();

        return $this;
    }

    /**
     * @param  array  $config
     */
    public function initConfig(array $config = [])
    {
        $this->config = $this->container['config'];
        $this->config->add(array_replace_recursive(
            [
                'environment'   => 'live',              // live, dev, test
                'locale'        => 'en',                // Default locale
                'locales'       => ['en'],              // Avalaible languages
                'session'       => [                    // Key at php.net/session.configuration, omit 'session.'
                    'name'          => '_gubug'
                ],
                'namespace'     => [
                    'component'     => '',
                    'module'        => '',
                    'plugin'        => '',
                    'theme'         => '',
                ],
                'controller'    => [
                    'main'          => '',              // PAC main agent
                    'error'         => '',
                    'default'       => ''               // Default component
                ],
                'path'          => [
                    'log'           => ROOT . 'temp/log/error.log'
                ],
                'serviceProvider'   => [],
                'routeCollection'   => [],
                'eventSubscriber'   => [],
            ],
            $config
        ));

        if ($env = ROOT . '.env' && is_file($env)) {
            $this->config->load($env, 'env');
        }

        $this->config->set('debug', $this->config->getBoolean(
            'debug',
            in_array($this->config->get('environment'), ['dev', 'test'])
        ));
    }

    public function initService()
    {
        // Setup
        $this->container['log.output'] = $this->config->get('path.log');
        $this->container['router.context']->fromRequest($this->container['request']);
        $this->container['resolver.controller']->param->set('namespace', $this->config->get('namespace'));
        $this->container['response']->prepare($this->container['request']);

        // Main
        $this->request    = $this->container['request'];
        $this->router     = $this->container['router'];
        $this->dispatcher = $this->container['dispatcher'];
        $this->response   = $this->container['response'];

        $this->session    = $this->container['session'];
        $this->event      = $this->container['event'];
        $this->log        = $this->container['log'];

        // Extra
        foreach ($this->config->get('serviceProvider', []) as $provider) {
            $this->container->register(new $provider());
        }
    }

    public function initSession()
    {
        $this->session->setOptions($this->config->get('session'));
        $this->session->start();
    }

    public function initEvent()
    {
        $this->event->addSubscriber(
            new EventListener\RouterListener(
                $this->router->urlMatcher,
                $this->container['request.stack'],
                $this->container['router.context']
            )
        );

        $this->event->addSubscriber(
            new EventListener\LocaleListener(
                $this->container['request.stack'],
                $this->config->get('locale'),
                $this->container['router.generator']
            )
        );

        if ($this->config->get('controller.error')) {
            $this->event->addSubscriber(
                new EventListener\ExceptionListener(
                    $this->config->get('controller.error'),
                    $this->log,
                    $this->config->get('debug')
                )
            );
        }
    }

    public function initRouter()
    {
        $this->router->param->set('routeDefaults', ['_locale' => $this->config->get('locale')]);
        $this->router->param->set('routeRequirements', ['_locale' => implode('|', $this->config->get('locales'))]);

        // Base
        $this->router->addRoute('base', '/', ['_controller' => $this->config->get('controller.default')]);
        if (count($this->config->get('locales')) > 1) {
            $this->router->addRoute('base_locale', '/{_locale}/', ['_controller' => $this->config->get('controller.default')]);
        }

        // Register routes
        foreach ($this->config->get('routeCollection', []) as $route) {
            $this->router->addRoute(...$route);
        }

        // Dynamic fallback
        if (count($this->config->get('locales')) > 1) {
            $this->router->addRoute('dynamic_locale', '/{_locale}/{_controller}', ['_controller' => $this->config->get('controller.default')], ['_controller' => '.*']);
        }
        $this->router->addRoute('dynamic', '/{_controller}', ['_controller' => $this->config->get('controller.default')], ['_controller' => '.*']);
    }

    public function run()
    {
        ServiceContainer::setStorage($this->container);

        if ($this->config->get('controller.main')) {
            list($class, $method) = explode('::', $this->config->get('controller.main'), 2);
            $this->response = call_user_func([new $class, $method]);
        } else {
            $this->response->setContent('Oops! looks like your app is not configured properly.');
        }

        $this->response->send();

        $this->dispatcher->terminate($this->request, $this->response);
    }
}
