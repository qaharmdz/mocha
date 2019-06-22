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

namespace Mocha\Admin\Component;

use Mocha\Controller;

/**
 * Application entrance handler
 *
 * @see  \Mocha\System\Framework  $config.system.controller.init
 */
class Init extends Controller
{
    public function index($data = [])
    {
        $this->event->trigger('init.alpha')->getData();
        // TODO: plugin from url alias to route $this->request->setPathInfo('/home/view/form'); // sample to manipulate requested component

        $response = $this->verifyAccess();

        if ($response && $response->hasOutput()) {
            return $response->getOutput();
        }

        // === Document
        $this->event->trigger('init.document')->getData();
        $this->registerAsset(); // TODO: change to plugin init.document

        $this->document->addNode('class_html', [
            'lang-'  . $this->language->get('lang_code'),
            'lang-'  . $this->language->get('lang_dir'),
            'debug-' . $this->config->get('setting.server.debug'),
            'env-'   . $this->config->get('setting.server.environment'),
        ]);
        $this->document->addNode('class_body', [
            'path-'  . $this->tool_utility->sanitizeChar($this->request->getPathInfo()),
            'theme-' . $this->config->get('setting.site.theme_admin'),
        ]);
        $this->document->addNode('breadcrumbs', [['Home', $this->router->url('home')]]);
        $this->document->addNode('alerts', $this->session->flash->get('alerts'));

        $this->document->loadAsset('form');

        $this->presenter->param->add(
            $this->event->trigger(
                'init.twig.global',
                ['global' => [
                    // Variables
                    'version'     => MOCHA,
                    'secure'      => $this->request->isSecure(),
                    'theme'       => $this->tool->metafile('theme', $this->config->get('setting.site.theme')),
                    'user'        => $this->user->all(),

                    // Objects
                    'config'      => $this->config,
                    'router'      => $this->router,
                    'document'    => $this->document,
                    'i18n'        => $this->language,
                    'tool_secure' => $this->tool_secure
                ]]
            )->getData()
        );
        // d($this->presenter->param->get('global'));

        // === Component
        $data = $this->event->trigger('init.content', $data)->getData();

        /**
         * Component event middleware
         *
         * @see \Symfony\Component\HttpKernel\KernelEvents
         * @see \Symfony\Component\HttpKernel\HttpKernel
         *
         * @return \Mocha\System\Engine\Response $component
         */
        // TODO: middleware "kernel.controller_arguments" to auto check view permission; change the component to error and pass new args
        $component = $this->event->trigger('init.component.response', [], $this->dispatcher->handle($this->request))->getOutput();

        if ($component->hasOutput()) {
            /**
             * @return \Mocha\System\Engine\Response
             */
            return $this->event->trigger('init.component.output', [], $component->getOutput())->getOutput();
        }

        $data['component'] = $component->hasContent() ? $component->getContent() : 'Content is not available.';

        // ===

        $this->document->setTitle(' | ' . $this->config->get('setting.site.site_name'), 'suffix');

        // Forwarding after login
        if (!$this->request->is('ajax')) {
            $this->session->set('last_route', $this->router->url($this->request->query->get('route', 'home')));
        }

        // === Block Layouts
        // Direct access controller, without event middleware
        /*
        d($this->config->all());
        d($this->event);
        TODO: at parent controller
            - $this->controller() load controller
            - $this->model(name, object) register model
            - $this->model(name, method) call model method wrapped in event

        d($this->controllerResolver->resolve('home', []));
        d($this->controllerResolver->resolve('cool/app', [], 'module'));
        d($this->event->getEmitters());
         */

        // === Presenter
        $template = $this->document->getNode('template_base', 'index');
        $response = $this->response
                        ->setStatusCode($component->getStatusCode())
                        ->setContent($this->tool->render(
                            $template,
                            $data,
                            'init.' . $template
                        ));

        /**
         * @return \Mocha\System\Engine\Response $response
         */
        $response = $this->event->trigger('init.omega', [], $response)->getOutput();

        if (!$this->config->get('setting.server.debug') && $this->config->get('setting.server.compression', 4)) {
            $response = $this->tool->compress($response);
        }


        return $response;
    }

    public function logout()
    {
        $this->user->logout();

        if ($this->request->is('ajax')) {
            return $this->response->jsonOutput(['redirect' => $this->router->url()], 401);
        }

        return $this->response->redirect($this->router->url());
    }

    protected function verifyAccess()
    {
        // Login page
        if ($this->request->getPathInfo() === '/') {
            return null;
        }

        // Config setting.server.debug is "true" in environment: dev and test
        if ($this->config->get('setting.server.debug')) {
            $this->config->set('setting.server.login_session', (60 * 6));
        }

        switch (true) {
            case !$this->user->isLogged():
                $this->session->flash->set('alert_login', true);
                return $this->logout();
                break;

            // All $_POST must have csrf
            case $this->request->is('post') && !$this->tool_secure->csrfValidate():
                if ($this->request->is('ajax')) {
                    return $this->response->jsonOutput(['message' => $this->language->get('error_csrf')], 403);
                }

                $this->document->addNode('alerts', [
                    ['warning', $this->language->get('error_csrf')],
                ]);

                return $this->response->redirect($this->session->get('last_route', $this->router->url('home')), 403);

            // Force logout if last activity more than 'x' minute
            case (time() - $this->session->get('user_activity')) > (60 * $this->config->get('setting.server.login_session')):
                $this->session->flash->set('alert_inactivity', true);
                return $this->logout();
                break;

            default:
                // Prevent session fixation. Renew session id per 30 minute
                if ((time() - $this->session->get('user_activity')) > (60 * 30)) {
                    $this->session->migrate();
                }

                $this->session->set('user_activity', time());
                break;
        }
    }

    private function registerAsset()
    {
        $this->document->addAsset('form', [
            'version'   => '4.22.0',
            'script'    => [
                $this->config->get('setting.url_site') . 'asset/script/form/form.min.js'
            ]
        ]);
        /*
        $this->document->addAsset('cookie', [
            'script'    => [
                $this->config->get('setting.url_site') . 'asset/script/cookie/cookie.min.js'
            ]
        ]);
        $this->document->addAsset('datepicker', [
            'script'    => [
                $this->config->get('setting.url_site') . 'asset/script/jquery-ui/ui.core.min.js',
                $this->config->get('setting.url_site') . 'asset/script/jquery-ui/datepicker.min.js'
            ]
        ]);
        $this->document->addAsset('datetimepicker', [
            'script'    => [
                $this->config->get('setting.url_site') . 'asset/script/jquery-ui/ui.core.min.js',
                $this->config->get('setting.url_site') . 'asset/script/jquery-ui/datepicker.min.js',
                $this->config->get('setting.url_site') . 'asset/script/jquery-ui/addons/timepicker.min.js'
            ]
        ]);
        $this->document->addAsset('ui-sortable', [
            'script'    => [
                $this->config->get('setting.url_site') . 'asset/script/jquery-ui/ui.core.min.js',
                $this->config->get('setting.url_site') . 'asset/script/jquery-ui/widget.min.js',
                $this->config->get('setting.url_site') . 'asset/script/jquery-ui/mouse.min.js',
                $this->config->get('setting.url_site') . 'asset/script/jquery-ui/sortable.min.js',
            ]
        ]);
        $this->document->addAsset('datatables', [
            'script'    => [
                $this->config->get('setting.url_site') . 'asset/script/datatables/dataTables.min.js',
                $this->config->get('setting.url_site') . 'asset/script/datatables/dataTables.columnFilter.min.js',
                $this->config->get('setting.url_site') . 'asset/script/datatables/dataTables.colVis.min.js',
                $this->config->get('setting.url_site') . 'asset/script/dataTables.config.min.js',
                $this->config->get('setting.url_site') . 'asset/script/typewatch/typewatch.min.js'
            ]
        ]);
        $this->document->addAsset('ckeditor', [
            'script'    => [
                $this->config->get('setting.url_site') . 'asset/script/ckeditor.config.min.js',
                $this->config->get('setting.url_site') . 'asset/script/ckeditor/ckeditor.js'
            ]
        ]);
        */
        $this->document->addAsset('select2', [
            'version'   => 'v4.0.6-rc.1',
            'style'     => [
                $this->config->get('setting.url_site') . 'asset/script/select2/select2.min.css'
            ],
            'script'    => [
                $this->config->get('setting.url_site') . 'asset/script/select2/select2.min.js'
            ]
        ]);
        /*
        $this->document->addAsset('slugify', [
            'script'    => [
                $this->config->get('setting.url_site') . 'asset/script/speakingurl/speakingurl.min.js'
            ]
        ]);
        $this->document->addAsset('simplyCountable', [
            'script'    => [
                $this->config->get('setting.url_site') . 'asset/script/simplyCountable/simplyCountable.min.js'
            ]
        ]);
        $this->document->addAsset('jstree', [
            'style'     => [
                $this->config->get('setting.url_site') . 'asset/script/jstree/themes/style.min.css'
            ],
            'script'    => [
                $this->config->get('setting.url_site') . 'asset/script/jstree/jstree.min.js'
            ]
        ]);
        $this->document->addAsset('dropzone', [
            'script'    => [
                $this->config->get('setting.url_site') . 'asset/script/dropzone/dropzone.min.js'
            ]
        ]);
         */
    }
}
