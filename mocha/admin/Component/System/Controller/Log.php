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

class Log extends \Mocha\Controller
{
    protected $error = [];

    public function index()
    {
        $data = [];

        $this->language->load('Component/System/setting');
        $this->language->load('Component/System/log');

        //=== Document
        $this->document->setTitle($this->language->get('page_title'));
        $this->document->addNode('breadcrumbs', [
            [$this->language->get('system')],
            [$this->language->get('nav_maintenance')],
            [$this->language->get('nav_mtc_error'), $this->router->url('system/log')]
        ]);

        //=== Content
        $log_file = $this->config->get('system.path.temp') . 'log' . DS . $this->config->get('setting.server.log_error');

        $data['navigations']    = $this->tool->controller('system/setting/navigation', ['log']);
        $data['log_content']    = is_file($log_file) ? file_get_contents($log_file) : '';

        // === Presenter
        return $this->response->setContent($this->tool->render(
            'Component/System/log',
            $data
        ));
    }

    public function download()
    {
        $this->language->load('Component/System/log');

        if (!$this->user->hasPermission('download', 'system/log')) {
            return $this->tool->errorAjax($this->language->get('error_permission_download'), 403);
        }

        $file = $this->config->get('system.path.temp') . 'log' . DS . $this->config->get('setting.server.log_error');
        $mask = $this->tool_utility->sanitizeChar(strtolower($this->config->get('setting.site.site_name'))) . '_' . $this->config->get('setting.server.log_error');

        if (is_file($file)) {
            return $this->response->fileOutput($file, $mask);
        }
    }

    public function clear()
    {
        $this->language->load('component/system/log');

        if (!$this->user->hasPermission('clear', 'system/log')) {
            return $this->tool->errorAjax($this->language->get('error_permission_clear'), 403);
        }

        $data = [];
        $file = $this->config->get('system.path.temp') . 'log' . DS . $this->config->get('setting.server.log_error');

        if (is_file($file) && filesize($file) > 0) {
            $handle = fopen($file, 'w');
            fclose($handle);
            $data['message'] = $this->language->get('success_clear');
        }

        return $this->response->jsonOutput($data);
    }
}
