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

namespace Mocha\Admin\Component;

class Init extends \Mocha\Controller
{
    public function index($data = [])
    {
        $response = $this->verifyAccess();

        if ($response && $response->hasOutput()) {
            return $response->getOutput();
        }

        $data = $this->event->trigger('init.start', $data)->getData();
        // TODO: plugin from url alias to route $this->request->setPathInfo('/home/test'); // sample to manipulate requested component

        // === Document
        $this->document->addNode('class_html', ['theme-' . $this->config->get('setting.site.theme_front')]);
        $this->document->addNode('breadcrumbs', [['Home', $this->router->url('home')]]);

        $this->document->applyAsset('form');

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
        /**
         * Component event middleware
         *
         * @see \Symfony\Component\HttpKernel\KernelEvents
         * @see \Symfony\Component\HttpKernel\HttpKernel
         *
         * @return \Mocha\System\Engine\Response $component
         */
        // TODO: middleware "kernel.controller_arguments" to auto check view permission; change the component to error and pass new args
        $component = $this->event->trigger('init.component', [], $this->dispatcher->handle($this->request))->getOutput();

        if ($component->hasOutput()) {
            /**
             * @return \Mocha\System\Engine\Response
             */
            return $this->event->trigger('init.component.output', [], $component->getOutput())->getOutput();
        }

        $data['component'] = $component->hasContent() ? $component->getContent() : 'Content is not available.';

        // ===

        $this->document->setTitle(' | Mocha', 'suffix');

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
         */

        // === Presenter
        $template = $this->document->getNode('template_base', 'index');

        return $this->response
            ->setStatusCode($component->getStatusCode())
            ->setContent($this->tool->render(
                $template,
                $data,
                'init.render.' . $template
            ));
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
            return;
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
            case (time() - $this->session->get('user_activity')) > (60 * $this->config->get('setting.server.login_session', 120)):
                $this->session->flash->set('alert_inactivity', 1);
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

        /*
        // All $_POST must have csrf
        if ($this->request->is('post') && !$this->tool_secure->csrfValidate()) {
            if ($this->request->is('ajax')) {
                return $this->response->jsonOutput(['message' => $this->language->get('error_csrf')], 403);
            }

            $this->document->addNode('alerts', [
                ['warning', $this->language->get('error_csrf')],
            ]);

            return $this->response->redirect($this->session->get('last_route', $this->router->url('home')), 403);
        }
         */

    }

}
