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

namespace Mocha\System\Library;

class Language
{
    protected $data = [];

    public function __construct()
    {
        $this->data = [
            'param'     => [
                'default'   => 'en',
                'active'    => 'en',
                'path'      => []
            ],
            'vars'      => [],
            'loaded'    => [],
        ];
    }

    public function param(array $param)
    {
        $this->data['param'] = array_replace_recursive(
            $this->data['param'],
            $param
        );
    }

    public function set(string $key, $value = '', string $group = '')
    {
        if ($group) {
            $this->data['vars'][$group]['i18n_' . $key] = $value;
        } else {
            $this->data['vars']['i18n_' . $key] = $value;
        }
    }

    public function get(string $key, string $group = '')
    {
        $vars = $this->data['vars'][$key] ?? $key;

        if ($group) {
            $vars = $this->data['vars'][$group][$key] ?? $key;
        }

        return $vars;
    }

    public function all()
    {
        return $this->data['vars'];
    }

    public function load(string $filename, string $group = '')
    {
        if (array_key_exists($filename, $this->data['loaded'])) {
            return $this->data['loaded'][$filename];
        }

        $files = [];
        $parts = explode('/', $filename);

        $file     = $filename;
        $filepart = '';
        if (count($parts) > 1) {
            $file     = array_pop($parts);
            $filepart = implode(DS, $parts) . DS;
        }

        $files = array_unique([
            $this->data['param']['path']['app'] . $filepart . 'Language' . DS .$this->data['param']['default'] . DS . $file . '.php',
            $this->data['param']['path']['language'] . $this->data['param']['default'] . DS . $filepart . $file . '.php',
            $this->data['param']['path']['app'] . $filepart . 'Language' . DS .$this->data['param']['active'] . DS . $file . '.php',
            $this->data['param']['path']['language'] . $this->data['param']['active'] . DS . $filepart . $file . '.php'
        ]);


        // Load file
        $variables = [];
        foreach ($files as $item) {
            if (is_file($item)) {
                $variables = array_merge($variables, (array)require($item));
            }
        }

        if (!$variables) {
            throw new \RuntimeException(sprintf('Language "%s" is not available', $filename));
        }

        $vars = [];
        foreach ($variables as $key => $value) {
            $vars['i18n_' . $key] = $value;
        }

        $this->data['loaded'][$filename] = $vars;
        $this->data['vars']     = array_merge(
            $this->data['vars'],
            $group ? [$group => $vars] : $vars
        );

        return $vars;
    }
}
