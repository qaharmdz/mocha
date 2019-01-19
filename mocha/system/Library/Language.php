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

use Symfony\Component\HttpFoundation\ParameterBag;

class Language
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected $storage;

    public function __construct(ParameterBag $bag)
    {
        $this->storage = $bag;

        $this->storage->add([
            'param'     => [
                'default'   => 'en',
                'active'    => 'en',
                'path'      => []
            ],
            'vars'      => [],
            'loaded'    => [],
        ]);
    }

    public function param(array $param)
    {
        $this->storage->add(['param' => $param]);
    }

    public function set(string $key, $value = '')
    {
        $this->storage->set('vars.' . $key, $value);
    }

    public function get(string $key, $default = null)
    {
        return $this->storage->get('vars.' . $key, $default);
    }

    public function all()
    {
        return $this->storage->get('vars');
    }

    public function load(string $filename, string $group = '')
    {
        if ($this->storage->has('loaded.' . $filename)) {
            return $this->storage->get('loaded.' . $filename);
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
            $this->storage->get('param.path.app') . $filepart . 'Language' . DS . $this->storage->get('param.default') . DS . $file . '.php',
            $this->storage->get('param.path.language') . $this->storage->get('param.default') . DS . $filepart . $file . '.php',
            $this->storage->get('param.path.app') . $filepart . 'Language' . DS . $this->storage->get('param.active') . DS . $file . '.php',
            $this->storage->get('param.path.language') . $this->storage->get('param.active') . DS . $filepart . $file . '.php'
        ]);

        // Load file
        $has_file  = false;
        $variables = [];
        foreach ($files as $item) {
            if (is_file($item)) {
                $has_file  = true;
                $variables = array_merge($variables, (array)require($item));
            }
        }

        if (!$has_file) {
            throw new \RuntimeException(sprintf('Language "%s" is not available', $filename));
        }

        $vars = [];
        foreach ($variables as $key => $value) {
            $vars[$key] = $value;
        }

        $this->storage->set('loaded.' . $filename, $vars);
        $this->storage->add(['vars' => $group ? [$group => $vars] : $vars]);

        return $vars;
    }
}
