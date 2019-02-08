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
        $this->verifyAccess();
        $this->verifyPermission();

        $data = $this->event->trigger('init.start', $data)->getData();
        // TODO: plugin from url alias to route $this->request->setPathInfo('/home/test'); // sample to manipulate requested component

        // === Document

        $this->document->addNode('class_html', ['theme-' . $this->config->get('setting.site.theme_front')]);
        $this->document->addNode('breadcrumb', [['Home', $this->router->url('home')]]);

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
        $component = $this->event->trigger('init.component', [], $this->dispatcher->handle($this->request))->getOutput();

        if ($component->hasOutput()) {
            /**
             * @return \Mocha\System\Engine\Response
             */
            return $this->event->trigger('init.component.output', [], $component->getOutput())->getOutput();
        }

        // _route_path solved inside event middleware
        if (!$this->request->is('ajax')) {
            $this->session->set('admin_login_forward', $this->router->url($this->request->attributes->get('_route_path', 'home')));
        }

        $this->document->setTitle(' | Mocha', 'suffix');

        $data['component'] = $component->hasContent() ? $component->getContent() : 'Content is not available.';

        // === Block Layouts

        // Direct access controller, without event middleware
        /*
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
                'init.' . $template
            ));
    }

    public function logout()
    {
        $this->user->logout();

        return $this->response->redirect($this->router->url());
    }

    protected function verifyAccess()
    {
        if (!$this->user->isLogged()) {
            $this->logout();
        }

        // Validate all post must have csrf
        if ($this->request->is('post') && !$this->tool_secure->csrfValidate()) {
            $this->session->flash->set('error_csrf', $this->language->get('error_csrf'));

            // Play it hard, remove all post after backup
            $this->request->attributes->add('request_post', $this->request->post->all());
            $this->request->post->replace([]);
        }
    }

    protected function verifyPermission()
    {}
}
