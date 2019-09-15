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

namespace Mocha\Admin\Component\Account\Controller;

use Mocha\Controller;
use Mocha\Admin\Component;

class User extends Controller
{
    public function index()
    {
        $data = [];

        $this->language->load('Component/Account/user');

        d($this->session->get('dtPost', []));
        d($this->tool_datatables->parse($this->session->get('dtPost', [])));

        //=== Document
        $this->document->setTitle($this->language->get('page_title'));
        $this->document->addNode('breadcrumbs', [
            [$this->language->get('accounts')],
            [$this->language->get('users')],
            [$this->language->get('list'), $this->router->url('account/user')]
        ]);

        $this->document->loadAsset('datatables');

        //=== Content
        $data['content'] = $this->language->get('message');

        // === Presenter
        return $this->response->setContent($this->tool->render(
            'Component/Account/user',
            $data
        ));
    }

    public function records()
    {
        if (!$this->request->is(['ajax', 'post'])) {
            return $this->tool->errorAjax($this->language->get('error_ajax_post'), 412);
        }

        $this->tool->abstractor('user', new Component\Account\Abstractor\User());

        $post = $this->request->post->all();

        $this->session->set('dtPost', $post);

        $records = $this->tool->abstractor('user.getRecords', [$post]);

        $data  = [];
        $count = count($records);
        for ($i=0; $i < $count; $i++) {
            $data[$i] = $records[$i];

            $data[$i]['DT_RowClass'] = 'dt-row-' . $data[$i]['user_id'];
            $data[$i]['raw']         = [
                'status'    => $data[$i]['status']
            ];

            $data[$i]['status']     = $this->language->get($data[$i]['status']);
            $data[$i]['created']    = $this->date->shift($data[$i]['created']);
            $data[$i]['last_login'] = $this->date->shift($data[$i]['last_login']);
            $data[$i]['url_edit']   = $this->router->url('account/userForm/edit', ['user_id' => $data[$i]['user_id']]);
        }

        $output = [
            'draw'            => (int)$post['draw'],
            'data'            => $data,
            'recordsFiltered' => count($data),
            'recordsTotal'    => $this->tool->abstractor('user.getTotalRecords'),
        ];

        return $this->response->jsonOutput($output);
    }

    public function logout()
    {
        $this->user->logout();

        $this->session->flash->set('alert_user_logout', true);

        return $this->response->redirect($this->router->url());
    }
}
