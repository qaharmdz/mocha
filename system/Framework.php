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
                'setting'       => [
                    'local'     => [
                        'timezone'      => 'UTC',
                        'language'      => 'en',
                        'languages'     => ['en' => []],
                    ],
                    'site'    => [
                        'theme'         => 'base'
                    ],
                    'server'    => [
                        'environment'   => 'live',
                        'session'       => [                    // Key at php.net/session.configuration, omit 'session.'
                            'name'      => '_mocha'
                        ],
                        'log_error'     => 'error.log',
                    ]
                ],
                'system'        => [
                    'version'       => '1.0.0-a.1',
                    'database'      => [
                        'charset'   => 'utf8',
                        'port'      => 3306
                    ],
                    'namespace'     => [
                        'component' => $config['app']['namespace'] . '\Component',
                        'module'    => $config['app']['namespace'] . '\Module',
                        'plugin'    => $config['app']['namespace'] . '\Plugin',
                        'theme'     => $config['app']['namespace'] . '\Theme'
                    ],
                    'controller'    => [
                        'main'      => $config['app']['namespace'] . '\Component\Main::index',
                        'error'     => $config['app']['namespace'] . '\Component\Error::index',
                        'default'   => 'Home' // 'Mocha\Front\Component\Home::index'
                    ],
                    'path'          => [
                        'app'           => $config['app']['path'],
                        'component'     => $config['app']['path'] . 'Component' . DS,
                        'module'        => $config['app']['path'] . 'Module' . DS,
                        'plugin'        => $config['app']['path'] . 'Plugin' . DS,
                        'language'      => $config['app']['path'] . 'Language' . DS,
                        'theme'         => $config['app']['path'] . 'Theme' . DS,
                        'asset'         => ROOT . 'asset' . DS,
                        'storage'       => ROOT . 'storage' . DS,
                        'system'        => ROOT . 'system' . DS,
                        'temp'          => ROOT . 'temp' . DS,
                    ],
                    'serviceProvider'   => [],
                    'eventSubscriber'   => [],
                    'routeCollection'   => [],
                ]
            ],
            $config
        ));

        // Setting from database
        $this->container['database.param'] = $this->config->get('system.database');
        $this->db = $this->container['database'];

        foreach ($this->db->get('setting') as $item) {
            $value = $item['serialized'] ? json_decode($item['value'], true) : $item['value'];

            if (!in_array($item['group'], ['app', 'system'])) {
                $this->config->set(implode('.', [$item['group'], $item['type'], $item['key']]), $value);
            }
        }

        // Adjustment
        $this->config->set('system.url_base', $this->config->get('setting.force_schema', $this->container['request']->getScheme()) . '://' . rtrim($this->config->get('system.url_site') . $this->config->get('app.url_part'), '/.\\')  . '/');
        $this->config->set('system.url_site', $this->config->get('setting.force_schema', $this->container['request']->getScheme()) . '://' . rtrim($this->config->get('system.url_site'), '/.\\')  . '/');
        $this->config->set('setting.local.language', $this->config->get('setting.local.language_' . $this->config->get('app.folder')));
        $this->config->set('setting.site.theme', $this->config->get('setting.site.theme_' . $this->config->get('app.folder')));
        $this->config->set('setting.server.debug', $this->config->getBoolean(
            'debug',
            in_array($this->config->get('setting.server.environment'), ['dev', 'test'])
        ));

        if ($env = ROOT . '.env' && is_file($env)) {
            $this->config->load($env, 'env');
        }

        date_default_timezone_set($this->config->get('setting.local.timezone'));
    }

    public function initService()
    {
        $this->container['log.output'] = $this->config->get('system.path.temp') . 'log' . DS . $this->config->get('setting.server.log_error');
        $this->container['router.context']->fromRequest($this->container['request']);
        $this->container['resolver.controller']->param->set('namespace', $this->config->get('system.namespace'));
        $this->container['response']->prepare($this->container['request']);

        $this->request    = $this->container['request'];
        $this->router     = $this->container['router'];
        $this->dispatcher = $this->container['dispatcher'];
        $this->response   = $this->container['response'];

        $this->session    = $this->container['session'];
        $this->event      = $this->container['event'];
        $this->log        = $this->container['log'];

        if ($this->config->get('setting.server.debug')) {
            Debug\Debug::enable(E_ALL, true);
        }

        $this->container['presenter']->param->add([
            'debug'     => $this->config->get('setting.server.debug'),
            'timezone'  => $this->config->get('setting.local.timezone'),
            'theme'     => [
                'active'    => $this->config->get('setting.site.theme')
            ],
            'path'      => [
                'app'       => $this->config->get('system.path.app'),
                'theme'     => $this->config->get('system.path.theme'),
                'cache'     => $this->config->get('system.path.temp') . 'twigs' . DS
            ]
        ]);

        // Extra
        foreach ($this->config->get('system.serviceProvider', []) as $provider) {
            $this->container->register(new $provider());
        }
    }

    public function initSession()
    {
        $this->session->setOptions($this->config->get('setting.server.session'));
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
                $this->config->get('setting.local.language'),
                $this->container['router.generator']
            )
        );

        if ($this->config->get('system.controller.error')) {
            $this->event->addSubscriber(
                new EventListener\ExceptionListener(
                    $this->config->get('system.controller.error'),
                    $this->log,
                    $this->config->get('setting.server.debug')
                )
            );
        }

        foreach ($this->config->get('system.eventSubscriber') as $subscriber) {
            $controller = $this->container['resolver.controller']->resolve($subscriber, [], 'Plugin');
            $this->event->addSubscriber(new $controller['class']());
        }
    }

    public function initRouter()
    {
        $this->router->param->set('routeDefaults', ['_locale' => $this->config->get('setting.local.language')]);
        $this->router->param->set('routeRequirements', ['_locale' => implode('|', array_keys($this->config->get('setting.local.languages')))]);

        // Base
        $this->router->addRoute('base', '/', ['_controller' => $this->config->get('system.controller.default')]);
        if (count($this->config->get('setting.local.languages')) > 1) {
            $this->router->addRoute('base_locale', '/{_locale}/', ['_controller' => $this->config->get('system.controller.default')]);
        }

        // Register routes
        foreach ($this->config->get('system.routeCollection', []) as $route) {
            $this->router->addRoute(...$route);
        }

        // Dynamic fallback
        if (count($this->config->get('setting.local.languages')) > 1) {
            $this->router->addRoute('dynamic_locale', '/{_locale}/{_controller}', ['_controller' => $this->config->get('system.controller.default')], ['_controller' => '.*']);
        }
        $this->router->addRoute('dynamic', '/{_controller}', ['_controller' => $this->config->get('system.controller.default')], ['_controller' => '.*']);
    }

    public function run()
    {
        Engine\ServiceContainer::setStorage($this->container);

        if ($this->config->get('system.controller.main')) {
            list($class, $method) = explode('::', $this->config->get('system.controller.main'), 2);

            $this->response = call_user_func([new $class, $method]);
        } else {
            $this->response->setContent('Oops! looks like your app is not configured properly.');
        }

        $this->response->send();

        $this->dispatcher->terminate($this->request, $this->response);
    }
}
