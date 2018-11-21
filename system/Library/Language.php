<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
                'app'       => [],
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

    public function set(string $key, $value = '')
    {
        $this->data['vars']['i18n_' . $key] = $value;
    }

    public function get($key)
    {
        return $this->data['vars'][$key] ?? $key;
    }

    public function all()
    {
        return $this->data['vars'];
    }

    public function load(string $filename)
    {
        if (!in_array($filename, $this->data['loaded'])) {
            // Load file
            $langs = [];
            if (is_file($item)) {
                $langs = array_merge($langs, (array)require($filename));
            }

            if (!$langs) {
                throw new \RuntimeException(sprintf('Language "%s" is not available', $filename));
            }

            $vars = [];
            foreach ($variables as $key => $value) {
                $vars['i18n_' . $key] = $value;
            }

            $this->data['loaded'][] = $filename;
            $this->data['vars']     = array_merge($this->data['vars'], $vars);
        }
    }
}
