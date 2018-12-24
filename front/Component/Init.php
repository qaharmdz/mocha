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

namespace Mocha\Front\Component;

class Init extends \Mocha\Controller
{
    public function index()
    {
        $data = $this->event->trigger('init.start', $this->language->load('general'))->getData();

        $this->document->addNode('class_html', ['theme-' . $this->config->get('setting.site.theme_front')]);

        // ========= Content

        // === Component
        // @todo plugin from url alias to route $this->request->setPathInfo('/home/test'); // sample to manipulate requested component

        /**
         * Component event middleware, see:
         * - \Symfony\Component\HttpKernel\KernelEvents
         * - \Symfony\Component\HttpKernel\HttpKernel
         *
         * @return \Mocha\System\Engine\Response $component
         */
        $component = $this->event->trigger('init.component', [], $this->dispatcher->handle($this->request))->getOutput();

        if ($component->hasOutput()) {
            /**
             * @return \Mocha\System\Engine\Response $component
             */
            return $this->event->trigger('init.component.output', [], $component->getOutput());
        }

        $data['component'] = $component->hasContent() ? $component->getContent() : 'Content is not available.';

        // === Block Layouts

        // Direct access controller, without event middleware
        // d($this->controller->resolve('home', []));
        // d($this->controller->resolve('cool/app', [], 'module'));

        // ========= Presenter

        $this->presenter->param->add(
            $this->event->trigger(
                'init.presenter.global',
                ['global' => [
                    'theme'     => $this->meta('theme', $this->config->get('setting.site.theme_front')),
                    'config'    => $this->config,
                    'router'    => $this->router,
                    'document'  => $this->document,
                    'request'   => [
                        'method'     => $this->request->getRealMethod(),
                        'secure'     => $this->request->isSecure(),
                        'post'       => $this->request->post,
                        'query'      => $this->request->query, // $_GET
                        'cookies'    => $this->request->cookies
                    ],
                ]]
            )->getData()
        );

        // d($data);
        // d($this->event);
        // d($this->config->all());
        // d($this->session->all());
        // $this->session->set('foo', 'bar');
        // d(
        //     $this->request->attributes->all(),
        //     $this->request->query->all()
        // );
        // d();
        // d($this->presenter->param->get('global'));

        // d(
        //     $this->user->login('admin@example.com', 'password'),
        //     $this->user->all()
        // );

        // d($this->event->getEmitters());

        return $this->response
            ->setStatusCode($component->getStatusCode())
            ->setContent($this->render(
                $this->document->getNode('template_base', 'index'),
                $data,
                'init.presenter'
            ));

        /*
        template_base
        - index   : Header, Footer, Block Layout
        - minimum : Header, Footer, no Block Layout
        - blank   : No Header, no Footer, no Block Layout
         */
    }
}
