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

namespace Mocha\Front\Component\Home\Controller;

use Mocha\Controller;

class Home extends Controller
{
    public function index()
    {
        $data = [];

        $this->language->load('Component/Home/home');

        $this->document->setTitle($this->language->get('page_title'));

        $data['content'] = $this->language->get('message');

        return $this->response->setContent($this->tool->render(
            'Component/Home/home',
            $data
        ));
    }

    public function test()
    {
        $data = [];

        $this->language->load('Component/Home/home');

        $this->document->setTitle($this->language->get('page_title'));

        $data['content'] = 'Method test';

        return $this->response->setContent($this->tool->render(
            'Component/Home/test',
            $data
        ));
    }
}
