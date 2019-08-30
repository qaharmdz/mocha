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

namespace Mocha\Admin\Component\System\Controller;

use Mocha\Controller;
use Mocha\Admin\Component;

class Setting extends Controller
{
    protected $error = [];

    public function index()
    {
        return $this->response->redirect($this->router->url('system/setting/site'));
    }

    public function site()
    {
        return $this->page('site');
    }

    public function server()
    {
        return $this->page('server');
    }

    public function locale()
    {
        return $this->page('locale');
    }

    protected function page($page)
    {
        $data = [];

        $this->language->load('Component/System/setting');
        $this->language->load('Component/System/' . $page);

        $this->tool->abstractor('setting', new Component\System\Abstractor\Setting());

        //=== Document
        $this->document->setTitle($this->language->get('page_title'));
        $this->document->addNode('breadcrumbs', [
            [$this->language->get('system')],
            [$this->language->get('nav_setting')],
            [$this->language->get('nav_setting_' . $page), $this->router->url('system/setting/' . $page)]
        ]);

        $this->document->loadAsset('select2');

        //=== Content
        $data['page_form']      = $this->form($page);
        $data['navigations']    = $this->navigation($page);

        // === Presenter
        return $this->response->setContent($this->tool->render(
            'Component/System/setting',
            $data
        ));
    }

    protected function form($page)
    {
        //=== Content
        $data['form_action']    = $this->router->url('system/setting/save');
        $data['form']           = $this->tool->abstractor('setting.getSettings', ['setting', $page]);

        if ($page === 'site') {
            $this->tool->abstractor('role', new Component\Account\Abstractor\Role());

            $data['roles'] = $this->tool->abstractor('role.getRoles');

            // TODO: check themes status at db extension
            $data['admin_themes'] = [];
            $admin_themes = glob(PATH_MOCHA . 'admin/Theme/*', GLOB_ONLYDIR);

            foreach ($admin_themes as $admin_theme) {
                if (is_file($admin_theme . '/metadata.php')) {
                    $data['admin_themes'][] = basename($admin_theme);
                }
            }

            // TODO: check themes status at db extension
            $data['front_themes'] = [];
            $front_themes = glob(PATH_MOCHA . 'front/Theme/*', GLOB_ONLYDIR);

            foreach ($front_themes as $front_theme) {
                if (is_file($front_theme . '/metadata.php')) {
                    $data['front_themes'][] = basename($front_theme);
                }
            }
        }
        if ($page === 'server') {
            $data['form']['cache_expire'] = $data['form']['cache_expire'] / 60;

            $data['ext_memcache']   = extension_loaded('memcache');
            $data['ext_memcached']  = extension_loaded('memcached');
            $data['ext_xcache']     = extension_loaded('xcache');
        }
        if ($page === 'locale') {
            $data['timezones']      = timezone_identifiers_list();
            $data['languages']      = $this->config->get('setting.locale.languages');
            $data['date_formats']   = ['M d, Y', 'F j, Y', 'd F Y', 'Y/m/d', 'm/d/Y', 'd/m/Y'];
            $data['time_formats']   = ['g:i A', 'g:i a', 'H:i'];
        }

        // === Presenter
        return $this->tool->render(
            'Component/System/' . $page,
            $data
        );
    }

    public function save()
    {
        $this->language->load('Component/System/setting');

        // === Validate
        if (!$this->user->hasPermission('edit', 'system/setting')) {
            return $this->tool->errorAjax($this->language->get('error_perm_edit'), 403);
        }

        if (!$this->request->is(['ajax', 'post'])) {
            return $this->tool->errorAjax($this->language->get('error_ajax_post_json'), 412);
        }

        $post = $this->request->post->all();
        if (!$this->validateForm($post)) {
            return $this->response->jsonOutput($this->error, 422);
        }

        // === Proceed
        $data = [
            'type'    => $post['setting_type'],
            'message' => sprintf($this->language->get('success_save_setting'))
        ];

        $this->tool->abstractor('setting', new Component\System\Abstractor\Setting());

        unset($post['setting_type']);

        if ($data['type'] == 'server') {
            $post['compression']    = min(max($post['compression'], 0), 9);
            $post['cache_expire']   = max($post['cache_expire'], 5) * 60;
        }

        if ($data['type'] == 'locale') {
            $post['date_custom_in']     = 0;
            $post['time_custom_in']     = 0;

            if ($post['date_format'] == '#custom') {
                $post['date_format']    = $post['date_custom'];
                $post['date_custom_in'] = 1;
            }

            if ($post['time_format'] == '#custom') {
                $post['time_format']    = $post['time_custom'];
                $post['time_custom_in'] = 1;
            }

            $post['datetime_format'] = $post['date_format'] . ' ' . $post['time_format'];
        }

        $this->tool->abstractor('setting.update', ['setting', $data['type'], $post]);

        return $this->response->jsonOutput($data);
    }

    public function navigation($page)
    {
        $this->language->load('Component/System/setting');

        $navigations = [
            100 => ['type' => 'header', 'title' => $this->language->get('nav_setting'),        'url' => '', 'active' => false, 'icon' => ''],
            101 => ['type' => 'in',     'title' => $this->language->get('nav_setting_site'),   'url' => $this->router->url('system/setting/site'),   'active' => $page === 'site',    'icon' => ''],
            102 => ['type' => 'in',     'title' => $this->language->get('nav_setting_locale'), 'url' => $this->router->url('system/setting/locale'), 'active' => $page === 'locale',  'icon' => ''],
            103 => ['type' => 'in',     'title' => $this->language->get('nav_setting_server'), 'url' => $this->router->url('system/setting/server'), 'active' => $page === 'server',  'icon' => ''],
            900 => ['type' => 'header', 'title' => $this->language->get('nav_maintenance'),    'url' => '', 'active' => false, 'icon' => ''],
            901 => ['type' => 'in',     'title' => $this->language->get('nav_mtc_sysinfo'),    'url' => $this->router->url('system/sysinfo'),        'active' => $page === 'sysinfo', 'icon' => ''],
            902 => ['type' => 'in',     'title' => $this->language->get('nav_mtc_error'),      'url' => $this->router->url('system/log'),            'active' => $page === 'log',     'icon' => ''],
        ];

        $navigations = $this->event->trigger('setting.navigation', $navigations)->getData();
        ksort($navigations);

        return $navigations;
    }

    protected function validateForm($post)
    {
        if ($post['setting_type'] == 'site') {
            if (!$this->valid->length(1, null)->validate($post['site_name'])) {
                $this->error[] = [
                    'element'   => 'site_name',
                    'message'   => $this->language->get('error_no_empty'),
                ];
            }
        }

        return empty($this->error);
    }
}
