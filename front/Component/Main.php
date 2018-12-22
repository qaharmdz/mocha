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

class Main extends \Mocha\Controller
{
    public function index()
    {
        $data = $this->language->load('general');

        $this->document->setTitle('Mocha - Pragmatic Content Management');
        $this->document->addNode('class_html', ['theme-' . $this->config->get('setting.site.theme_front')]);

        // ========= Content

        // === Theme

        // === Component

        /**
         * Component wrapped in middleware events, details see:
         * - \Symfony\Component\HttpKernel\KernelEvents
         * - \Symfony\Component\HttpKernel\HttpKernel::handleRaw
         */
        $component = $this->dispatcher->handle($this->request);

        if ($component->hasOutput()) {
            return $component->getOutput();
        }

        $data['component'] = $component->hasContent() ? $component->getContent() : 'Content is not available.';

        // Direct access controller, without event middleware
        // d($this->controller->resolve('home', []));
        // d($this->controller->resolve('cool/app', [], 'module'));

        // === Block Layouts

        // ========= Presenter

        $this->presenter->param->add(['global' => [
            'theme'     => $this->meta('theme', $this->config->get('setting.site.theme_front')),
            'config'    => $this->config,
            'router'    => $this->router,
            'document'  => $this->document,
            'request'   => [
                'method'    => $this->request->getRealMethod(),
                'secure'    => $this->request->isSecure(),
                'post'      => $this->request->post,
                'query'     => $this->request->query, // $_GET
                'cookies'   => $this->request->cookies
            ],
        ]]);

        // d($data);
        // d($this->event);
        // d($this->config->all());
        // d($this->presenter->param->get('global'));
        // d(
        //     $this->router->urlGenerate(),
        //     $this->router->urlGenerate('home'),
        //     $this->router->urlGenerate('blog/category'),
        //     $this->router->urlGenerate('blog/post', ['id' => 2, 'ref' => 'home'])
        // );

        return $this->response
            ->setStatusCode($component->getStatusCode())
            ->setContent($this->presenter->render(
                $this->document->getNode('template_base', 'index'),
                $data
            ));
    }
}

/*
Layout
- Blank   : No header, no footer, white canvas
- Minimum : Header, Footer
- index   : default
 */
