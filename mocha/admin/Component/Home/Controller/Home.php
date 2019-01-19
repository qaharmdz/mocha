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

namespace Mocha\Admin\Component\Home\Controller;

class Home extends \Mocha\Controller
{
    public function index()
    {
        $data = [];

        $this->language->load('Component/Home/home');

        $this->document->setTitle($this->language->get('page_title'));
        $this->document->addNode('class_body', ['page-home']);

        if ($this->request->query->get('view')) {
            if ($this->request->query->get('view') == 'listing') {
                $this->document->addNode('class_body', ['layout-tab']);
            }

            $this->document->addNode('breadcrumb', [
                ['Content'],
                ['Posts', $this->router->url('content/post')],
                ['Insert', $this->router->url('content/post/form/id/0')],
                ['Edit #12', $this->router->url('content/post/form/id/12')]
            ]);
        }

        $data['content'] = $this->language->get('message');

        return $this->response->setContent($this->tool->render(
            'Component/Home/' . $this->request->query->get('view', 'home'),
            $data
        ));
    }
}
