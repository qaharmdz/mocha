<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

        // ====== Component

        $component = $this->dispatcher->handle($this->request);

        if ($component->hasOutput()) {
            return $component->getOutput();
        }

        $data['component'] = $component->hasContent() ? $component->getContent() : 'No component content';

        // Module Positions

        // ====== Presenter

        $this->presenter->param->add(['global' => [
            'config'    => $this->config,
            'request'   => [
                'method'    => $this->request->getRealMethod(),
                'post'      => $this->request->post,
                'query'     => $this->request->query, // $_GET
                'cookies'   => $this->request->cookies
            ],
            'router'    => $this->router,
            'document'  => $this->document,
        ]]);

d($this->event);
        // d($data);
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
