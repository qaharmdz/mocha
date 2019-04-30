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

namespace Mocha\Admin\Component\Home\Controller;

class Home extends \Mocha\Controller
{
    public function index()
    {
        $data = [];

        $this->language->load('Component/Home/home');

        $this->document->setTitle($this->language->get('page_title'));
        $this->document->addNode('alerts', [
            ['primary', 'Sketsa all about UIkit custom theme, extra components and layout experiment.<br>Detailed information on UIkit usage please refer to it <a href="https://getuikit.com/docs/" target="_blank">documentation</a>.'],
            ['warning', 'First page</p><p>Second page'],
            ['danger uk-alert-small uk-alert-noclose', '<span class="uk-float-left" uk-icon="icon:warning;ratio:1.6"></span> <span class="uk-margin-medium-left uk-display-block">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repellendus quisquam deleniti eos unde dolor.</span>'],
            ['danger uk-background-red uk-contrast uk-alert-small uk-alert-noclose', '<span class="uk-float-left"><i data-feather="alert-triangle" width="32px" height="32px"></i></span> <span class="uk-margin-medium-left uk-display-block">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repellendus quisquam deleniti eos unde dolor. Dicta corrupti ullam aperiam maiores, neque cum ut. Sapiente neque maxime odio debitis, aliquid, laudantium rem?</span>'],
        ]);

        if ($this->request->query->get('view')) {
            if ($this->request->query->get('view') == 'listing') {
                $this->document->addNode('class_body', ['layout-tab']);
            }

            $this->document->addNode('breadcrumbs', [
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
