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
     * @var \Mocha\System\Engine\Response
     */
    public $response;

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
                        'timezone'      => 'UTC', // Timezone on display
                        'language'      => 'en',
                        'languages'     => ['en' => [
                            'id'    => 1
                        ]],
                    ],
                    'site'    => [
                        'theme'         => 'base'
                    ],
                    'server'    => [
                        'environment'   => 'live',
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
                        'init'      => $config['app']['namespace'] . '\Component\Init::index',
                        'error'     => $config['app']['namespace'] . '\Component\Error::index',
                        'default'   => 'Home' // 'Mocha\Front\Component\Home::index'
                    ],
                    'session'       => [ // Key at php.net/session.configuration, omit 'session.'
                        'name'              => 'mocha',
                        'use_cookies'       => 1,
                        'use_only_cookies'  => 1,
                        'use_strict_mode'   => 1,
                        'cookie_httponly'   => 1,
                        'use_trans_sid'     => 0,
                        'sid_length'        => rand(48, 64)
                    ],
                    'path'          => [
                        'root'              => ROOT,
                        'app'               => $config['app']['path'],
                        'component'         => $config['app']['path'] . 'Component' . DS,
                        'module'            => $config['app']['path'] . 'Module' . DS,
                        'plugin'            => $config['app']['path'] . 'Plugin' . DS,
                        'language'          => $config['app']['path'] . 'Language' . DS,
                        'theme'             => $config['app']['path'] . 'Theme' . DS,
                        'asset'             => ROOT . 'asset' . DS,
                        'storage'           => ROOT . 'storage' . DS,
                        'system'            => ROOT . 'system' . DS,
                        'temp'              => ROOT . 'temp' . DS,
                    ],
                    'serviceProvider'       => [
                        '\Mocha\System\Tool\ProviderTool' // @todo: move to each app folder \Mocha\ucfirst($config['app']['folder'])\ProviderTool
                    ],
                    'eventSubscriber'       => [],
                    'routeCollection'       => [],
                ]
            ],
            $config
        ));

        // Update setting from database
        $this->container['database.param'] = $this->config->get('system.database');
        $this->config->remove('system.database');

        foreach ($this->container['database']->where('`group`', 'setting')->get('setting') as $item) {
            $this->config->set(
                implode('.', [$item['group'], $item['type'], $item['key']]),
                $item['serialized'] ? json_decode($item['value'], true) : $item['value']
            );
        }

        // @todo: Update serviceProvider, eventSubscriber, routeCollection with plugins
        /*
        Use `key` to store plugin_id and check if plugin is enabled
        d($this->container['database']->where('`group`', 'system')->get('setting'));
         */

        // @todo: Update languages

        // Adjustment
        if ($this->config->get('setting.server.secure')) {
            $this->container['request']->server->set('HTTPS', true);
        }

        $this->config->set('setting.url_site', $this->container['request']->getScheme() . '://' . rtrim($this->config->get('setting.url_site'), '/.\\')  . '/');
        $this->config->set('setting.url_base', rtrim($this->config->get('setting.url_site') . $this->config->get('app.url_part'), '/.\\')  . '/');

        $this->config->set('setting.local.language', $this->config->get('setting.local.language_' . $this->config->get('app.folder')));
        $this->config->set('setting.local.language_id', $this->config->get('setting.local.languages')[$this->config->get('setting.local.language')]['id']);
        $this->config->set('setting.site.theme', $this->config->get('setting.site.theme_' . $this->config->get('app.folder')));
        $this->config->set('setting.server.debug', $this->config->getBoolean(
            'setting.server.debug',
            in_array($this->config->get('setting.server.environment'), ['dev', 'test'])
        ));

        if ($env = ROOT . '.env' && is_file($env)) {
            $this->config->load($env, 'env');
        }

        // Standarize php and database timezone to UTC
        date_default_timezone_set('UTC');
        $this->container['database']->rawQuery('SET time_zone="+00:00";');

        return $this;
    }

    public function initService()
    {
        $this->container['log.output'] = $this->config->get('system.path.temp') . 'log' . DS . $this->config->get('setting.server.log_error');
        $this->container['router.context']->fromRequest($this->container['request']);
        $this->container['resolver.controller']->param->set('namespace', $this->config->get('system.namespace'));
        $this->container['controller'] = $this->container['resolver.controller']; // alias
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
        $this->container['language']->param([
            'active'    => $this->config->get('setting.local.language'),
            'path'      => $this->config->get('system.path')
        ]);

        if ($this->config->get('setting.server.debug')) {
            Debug\Debug::enable(E_ALL, true);
        }

        // Extra
        foreach ($this->config->get('system.serviceProvider', []) as $provider) {
            $this->container->register(new $provider());
        }

        return $this;
    }

    public function initSession()
    {
        $this->container['session.options'] = $this->config->get('system.session');
        $this->container['session']->start();

        return $this;
    }

    public function initEvent()
    {
        $this->container['event']->addSubscriber(
            new EventListener\RouterListener(
                $this->container['router']->urlMatcher,
                $this->container['request.stack'],
                $this->container['router.context']
            )
        );

        $this->container['event']->addSubscriber(
            new EventListener\LocaleListener(
                $this->container['request.stack'],
                $this->config->get('setting.local.language'),
                $this->container['router.generator']
            )
        );

        if ($this->config->get('system.controller.error')) {
            $this->container['event']->addSubscriber(
                new EventListener\ExceptionListener(
                    $this->config->get('system.controller.error'),
                    $this->container['log'],
                    $this->config->get('setting.server.debug')
                )
            );
        }

        foreach ($this->config->get('system.eventSubscriber') as $subscriber) {
            $controller = $this->container['resolver.controller']->resolve($subscriber, [], 'Plugin');
            $this->container['event']->addSubscriber(new $controller['class']());
        }

        return $this;
    }

    public function initRouter()
    {
        $this->container['router']->param->set('routeDefaults', ['_locale' => $this->config->get('setting.local.language')]);
        $this->container['router']->param->set('routeRequirements', ['_locale' => implode('|', array_keys($this->config->get('setting.local.languages')))]);

        // Base
        $this->container['router']->addRoute('_base', '/', ['_controller' => $this->config->get('system.controller.default')]);
        if (count($this->config->get('setting.local.languages')) > 1) {
            $this->container['router']->addRoute('base_locale', '/{_locale}/', ['_controller' => $this->config->get('system.controller.default')]);
        }

        // Register routes
        foreach ($this->config->get('system.routeCollection', []) as $route) {
            $this->container['router']->addRoute(...$route);
        }

        // Dynamic fallback
        if (count($this->config->get('setting.local.languages')) > 1) {
            $this->container['router']->addRoute('dynamic_locale', '/{_locale}/{_controller}', ['_controller' => $this->config->get('system.controller.default')], ['_controller' => '.*']);
        }
        $this->container['router']->addRoute('_dynamic', '/{_controller}', ['_controller' => $this->config->get('system.controller.default')], ['_controller' => '.*']);

        return $this;
    }

    public function run()
    {
        Engine\ServiceContainer::storage($this->container);

        $this->response = $this->container['response'];

        if ($this->config->get('system.controller.init')) {
            list($class, $method) = explode('::', $this->config->get('system.controller.init'), 2);

            $this->response = call_user_func([new $class, $method]);
        } else {
            $this->response->setContent('Oops! looks like your app is not configured properly.');
        }

        $this->response->send();

        $this->container['dispatcher']->terminate($this->container['request'], $this->response);
    }
}
