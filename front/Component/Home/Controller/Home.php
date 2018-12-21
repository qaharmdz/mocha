<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mocha\Front\Component\Home\Controller;

class Home extends \Mocha\Controller
{
    public function index()
    {
        $this->language->load('Component/Home/home');

        $this->document->setTitle($this->language->get('i18n_page_title'));
        $this->document->addNode('class_body', ['page-home']);

        $data = $this->language->all();
        $data['content'] = $this->language->get('i18n_message');

        return $this->response->setContent($this->presenter->render(
            'Component/Home/home',
            $data
        ));
    }
}
