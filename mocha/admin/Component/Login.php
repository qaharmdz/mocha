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

class Login extends \Mocha\Controller
{
    protected $error =[];

    public function index($data = [])
    {
        $this->user->logout(); // Force logout
        $this->language->load('login');

        // === Document
        $this->document->setTitle($this->language->get('page_title'));
        $this->document->addNode('class_body', ['path-login']);

        // === POST
        if ($this->request->is('post') && $this->validateForm()) {
            return $this->response->redirect($this->session->get('last_route', $this->router->url('home')), 303);
        }

        // === Alert
        $alertSession = [
            'alert_login'        => 'danger',
            'alert_admin_access' => 'danger',
            'alert_token'        => 'warning',
            'alert_inactivity'   => 'warning',
            'alert_logout'       => 'success'
        ];

        foreach ($alertSession as $key => $value) {
            if ($this->session->flash->get($key)) {
                ${'alert_'.$value} = $this->language->get($key);
            }
        }

        $data['alerts'] = ['primary', 'success', 'danger', 'warning'];
        foreach ($data['alerts'] as $alert) {
            $data['alert_' . $alert] = $this->error['alert_'.$alert] ?? ${'alert_'.$alert} ?? '';
        }

        // === Content
        $data['email']          = $this->request->post->get('email', '');
        $data['password']       = $this->request->post->get('password', '');

        // === Presenter
        return $this->response
            ->setContent($this->tool->render('login', $data))
            ->setOutput();
    }

    protected function validateForm()
    {
        if (!$this->tool_secure->csrfValidate()) {
            return $this->error['alert_danger'] = $this->language->get('error_csrf');
        }

        if (!$this->user->login($this->request->post->get('email'), $this->request->post->get('password'))) {
            $this->error['alert_warning'] = $this->language->get('alert_mail_pass');
        }

        if (empty($this->error) && !$this->user->hasPermission('backend')) {
            $this->error['alert_danger'] = $this->language->get('alert_backend_access');
        }

        return !$this->error;
    }
}
