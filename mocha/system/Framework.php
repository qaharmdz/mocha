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
        $this->initEnvironment();
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
                    'locale'    => [
                        'timezone'      => 'UTC', // Timezone on display
                        'language'      => 'en',
                        'languages'     => [ // TODO: update the list with installed languages
                            'en' => [
                                'language_id' => 1,
                                'code'        => 'en',
                                'name'        => 'English'
                            ]
                        ],
                    ],
                    'site'      => [
                        'theme'         => 'base'
                    ],
                    'server'    => [
                        'environment'   => 'live',
                        'debug'         => false,
                        'secure'        => false, // force https
                        'log_error'     => 'error.log',
                    ]
                ],
                'system'        => [
                    'version'       => MOCHA,
                    'database'      => [
                        'charset'       => 'utf8',
                        'port'          => 3306
                    ],
                    'namespace'     => [
                        'component'     => $config['app']['namespace'] . '\Component',
                        'module'        => $config['app']['namespace'] . '\Module',
                        'plugin'        => $config['app']['namespace'] . '\Plugin',
                        'theme'         => $config['app']['namespace'] . '\Theme'
                    ],
                    'controller'    => [
                        'init'          => $config['app']['namespace'] . '\Component\Init::index',
                        'error'         => $config['app']['namespace'] . '\Component\Error::index',
                        'default'       => $config['app']['controller'] ?? 'Home'
                    ],
                    'path'          => [
                        'root'          => PATH_ROOT,
                        'app'           => $config['app']['path'],
                        'component'     => $config['app']['path'] . 'Component' . DS,
                        'module'        => $config['app']['path'] . 'Module' . DS,
                        'plugin'        => $config['app']['path'] . 'Plugin' . DS,
                        'language'      => $config['app']['path'] . 'Language' . DS,
                        'theme'         => $config['app']['path'] . 'Theme' . DS,
                        'asset'         => PATH_MOCHA . 'asset' . DS,
                        'storage'       => PATH_MOCHA . 'storage' . DS,
                        'system'        => PATH_MOCHA . 'system' . DS,
                        'temp'          => PATH_MOCHA . 'temp' . DS,
                    ],
                    'headers'       => [ // Additional response headers
                        'Referrer-Policy'        => 'no-referrer-when-downgrade',
                        'X-Content-Type-Options' => 'nosniff',
                        'X-Frame-Options'        => 'sameorigin',
                        'X-XSS-Protection'       => '1; mode=block'
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
                    'serviceProvider'       => [
                        '\Mocha\System\Tool\ProviderTool'
                    ],
                    'eventSubscriber'       => [],
                    'routeCollection'       => [],
                ]
            ],
            $config
        ));

        return $this;
    }

    public function initEnvironment()
    {
        $this->container['database_param'] = $this->config->get('system.database');
        $this->config->remove('system.database');

        // Standardize PHP and database timezone MUST be UTC
        date_default_timezone_set('UTC');
        $this->container['db']->rawQuery('SET time_zone="+00:00";');

        // ====== Update config

        // Load setting from database
        foreach ($this->container['db']->where('`group`', 'setting')->get('setting') as $item) {
            $this->config->set(
                implode('.', [$item['group'], $item['type'], $item['key']]),
                $item['encoded'] ? json_decode($item['value'], true) : $item['value']
            );
        }

        // Config adjustment
        $this->config->set('setting.url_site', $this->container['request']->getScheme() . '://' . rtrim($this->config->get('setting.url_site'), '/.\\')  . '/');
        $this->config->set('setting.url_base', rtrim($this->config->get('setting.url_site') . $this->config->get('app.url_part'), '/.\\')  . '/');

        $this->config->set('setting.site.theme', $this->config->get('setting.site.theme_' . $this->config->get('app.folder')));
        $this->config->set('setting.locale.language', $this->config->get('setting.locale.language_' . $this->config->get('app.folder')));
        $this->config->set('setting.locale.language_id', $this->config->get('setting.locale.languages')[$this->config->get('setting.locale.language')]['language_id']);

        if ($this->config->get('setting.server.environment') != 'live') {
            $this->config->set('setting.server.debug', true);
        }

        // ====== Environment setting (possible to replace all config)
        if ($env = PATH_PUBLIC . '.env' && is_file($env)) {
            $this->config->load($env, 'env');
        }

        if (!$this->config->get('setting.server.debug')) {
            // Disabled Kint in production (live) environment
            \Kint::$enabled_mode = false;
        }

        // TODO: Update languages list

        // ====== TODO: Update serviceProvider, eventSubscriber, routeCollection list with plugins
        /*
        Use `key` to store plugin_id and check if plugin is enabled
        d($this->container['db']->where('`group`', 'system')->get('setting'));
         */

        // ====== Symlinks public assets
        // TODO: do not use symlinks, think a way to use actual folder
        if ($this->config->getBoolean('system.symlink.status', true)) {
            $symlinks = $this->config->get('system.symlink.path', [
                $this->config->get('system.path.storage') . 'image' => PATH_PUBLIC . '_image',      // Uploaded image
                $this->config->get('system.path.temp') . 'image'    => PATH_PUBLIC . '_images',     // Cache and resized image
                $this->config->get('system.path.asset')  => PATH_PUBLIC . '_asset',      // General asset
                $this->config->get('system.path.theme') . $this->config->get('setting.site.theme_admin') . DS . 'asset' => PATH_PUBLIC . '_th' . $this->config->get('setting.site.theme_admin'),
                $this->config->get('system.path.theme') . $this->config->get('setting.site.theme_front') . DS . 'asset' => PATH_PUBLIC . '_th' .$this->config->get('setting.site.theme_front')
            ]);

            foreach ($symlinks as $real => $link) {
                if (file_exists($real) && (!is_link($link) || readlink($link) != $real)) {
                    try {
                        if (file_exists($link)) {
                            @unlink($link);
                        }

                        if (!file_exists($link)) {
                            symlink($real, $link);
                        }
                    } catch (\Exception $e) {
                        exit('DEPRESSO | The feeling you get when you run out of coffee!');
                    }
                }
            }
        }

        return $this;
    }

    public function initService()
    {
        if ($this->config->get('setting.server.debug')) {
            Debug\Debug::enable(E_ALL, true);
        }

        if ($this->config->get('setting.server.secure')) {
            $this->container['request']->server->set('HTTPS', true);
        }

        $this->container['log_output'] = $this->config->get('system.path.temp') . 'log' . DS . $this->config->get('setting.server.log_error');
        $this->container['router_context']->fromRequest($this->container['request']);
        $this->container['resolver_controller']->param->set('namespace', $this->config->get('system.namespace'));
        $this->container['response']->headers->add($this->config->get('system.headers'));

        $this->container['router']->param->add([
            'routeDefaults'     => ['_locale' => 'en'],
            'routeRequirements' => ['_locale' => 'en'],
            'buildLocale'       => count($this->config->get('setting.locale.languages')) > 1
        ]);

        $this->container['presenter']->param->add([
            'debug'     => $this->config->get('setting.server.debug'),
            'timezone'  => $this->config->get('setting.locale.timezone'),
            'theme'     => [
                'default'   => $this->config->get('app.folder') == 'admin' ? 'pawon' : 'pendapa',
                'active'    => $this->config->get('setting.site.theme')
            ],
            'path'      => [
                'app'       => $this->config->get('system.path.app'),
                'theme'     => $this->config->get('system.path.theme'),
                'cache'     => $this->config->get('system.path.temp') . 'twigs' . DS
            ]
        ]);

        $this->container['language']->param([
            'active'    => $this->config->get('setting.locale.language'),
            'path'      => $this->config->get('system.path')
        ]);
        $this->container['language']->load('general');

        $this->container['date']->param->add([
            // TODO: more date param from database setting
            'localize_datetime' => $this->container['language']->get('i18n_localize_datetime')
        ]);

        // Extra service
        foreach ($this->config->get('system.serviceProvider', []) as $provider) {
            $this->container->register(new $provider());
        }

        return $this;
    }

    public function initSession()
    {
        $this->container['session_option'] = $this->config->get('system.session');
        $this->container['session']->start();

        // A 'user' visitor token
        if (!$this->container['session']->has('user_token')) {
            $this->container['session']->set(
                'user_token',
                $this->container['secure']->generateCode('hash', rand(12, 24))
            );
        }

        return $this;
    }

    public function initEvent()
    {
        $this->container['event']->addSubscriber(
            new EventListener\RouterListener(
                $this->container['router']->urlMatcher,
                $this->container['request_stack'],
                $this->container['router_context']
            )
        );

        $this->container['event']->addSubscriber(
            new EventListener\LocaleListener(
                $this->container['request_stack'],
                $this->config->get('setting.locale.language'),
                $this->container['router_generator']
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
            $controller = $this->container['resolver_controller']->resolve($subscriber, [], 'Plugin');
            $this->container['event']->addSubscriber(new $controller['class']());
        }

        return $this;
    }

    public function initRouter()
    {
        $this->container['router']->param->set('routeDefaults', ['_locale' => $this->config->get('setting.locale.language')]);
        $this->container['router']->param->set('routeRequirements', ['_locale' => implode('|', array_keys($this->config->get('setting.locale.languages')))]);

        // Base
        $this->container['router']->addRoute('_base', '/', ['_controller' => $this->config->get('system.controller.default')]);
        if (count($this->config->get('setting.locale.languages')) > 1) {
            $this->container['router']->addRoute('_base_locale', '/{_locale}/', ['_controller' => $this->config->get('system.controller.default')]);
        }

        // Register routes
        foreach ($this->config->get('system.routeCollection', []) as $route) {
            $this->container['router']->addRoute(...$route);
        }

        // Dynamic fallback
        if (count($this->config->get('setting.locale.languages')) > 1) {
            $this->container['router']->addRoute('_dynamic_locale', '/{_locale}/{_controller}', ['_controller' => $this->config->get('system.controller.default')], ['_controller' => '.*']);
        }
        $this->container['router']->addRoute('_dynamic', '/{_controller}', ['_controller' => $this->config->get('system.controller.default')], ['_controller' => '.*']);

        return $this;
    }

    public function run()
    {
        Engine\ServiceContainer::storage($this->container);

        if ($this->config->get('system.controller.init')) {
            list($class, $method) = explode('::', $this->config->get('system.controller.init'), 2);
            $response = call_user_func([new $class, $method]);
        } else {
            $response->setContent('Oops! looks like your app is not configured properly.');
        }

        $response->send();

        $this->container['dispatcher']->terminate($this->container['request'], $response);
    }
}
