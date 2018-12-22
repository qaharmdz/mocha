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
    public function index(array $data = [])
    {
        $this->language->load('general');

        $this->document->setTitle('Mocha - Pragmatic Content Management');
        $this->document->addNode('class_html', ['theme-' . $this->config->get('setting.site.theme_front')]);

        $data = array_replace_recursive($data, $this->language->all());

        // ====== Content

        // === Theme

        d($this->config->get('system.path.theme') . $this->config->get('setting.site.theme_front') . DS . 'metadata.php');
        if (is_file($this->config->get('system.path.theme') . $this->config->get('setting.site.theme_front') . DS . 'metadata.php')) {
            d($this->config->all());
        }

        // === Component

        /**
         * Components wrapped by kernel events, details see:
         * - \Symfony\Component\HttpKernel\KernelEvents
         * - \Symfony\Component\HttpKernel\HttpKernel::handleRaw
         */
        $component = $this->dispatcher->handle($this->request);

        if ($component->hasOutput()) {
            return $component->getOutput();
        }

        $data['component'] = $component->hasContent() ? $component->getContent() : 'Content isn\'t available.';

        // === Block Layouts
        // Module Positions

        // ====== Presenter

        $this->presenter->param->add(['global' => [
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
