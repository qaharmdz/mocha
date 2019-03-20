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

namespace Mocha\Admin\Component\System\Controller;

use Mocha\Admin\Component as C;

class Setting extends \Mocha\Controller
{
    public function index()
    {
        d($this->request->query->all());
        // return $this->response->redirect($this->router->url('system/setting/site'));
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

        $this->tool->abstractor('setting', new C\System\Abstractor\Setting());

        //=== Document

        $this->document->setTitle($this->language->get('page_title'));
        $this->document->addNode('class_body', ['page-system-' . $page, 'com-system', 'layout-tab']);

        $this->document->addNode('breadcrumb', [
            [$this->language->get('system')],
            [$this->language->get('setting')],
            [$this->language->get('nav_' . $page), $this->router->url('system/setting/' . $page)]
        ]);

        //=== Content

        $data['navigations'] = [];
        foreach (['site', 'server', 'locale'] as $nav) {
            $data['navigations'][] = [
                'url'       => $this->router->url('system/setting/' . $nav),
                'title'     => $this->language->get('nav_' . $nav),
                'active'    => $page === $nav
            ];
        }

        $data['form_action']    = $this->router->url('system/setting/save');
        $data['form']           = $this->tool->abstractor('setting.getSettings', ['setting', $page]);

        if ($page === 'site') {
            $this->tool->abstractor('role', new C\Account\Abstractor\Role());

            $data['roles'] = $this->tool->abstractor('role.getRoles');

            // TODO: check themes status at db extension
            $data['admin_themes'] = [];
            $admin_themes = glob(PATH_MOCHA . 'admin/Theme/*', GLOB_ONLYDIR);

            foreach ($admin_themes as $admin_theme) {
                if (is_file($admin_theme . '/metadata.php')) {
                    $data['admin_themes'][] = basename($admin_theme);
                }
            }

            $data['front_themes'] = [];
            $front_themes = glob(PATH_MOCHA . 'front/Theme/*', GLOB_ONLYDIR);

            foreach ($front_themes as $front_theme) {
                if (is_file($front_theme . '/metadata.php')) {
                    $data['front_themes'][] = basename($front_theme);
                }
            }
        }

        return $this->response->setContent($this->tool->render(
            'Component/System/' . $page,
            $data
        ));
    }
}
