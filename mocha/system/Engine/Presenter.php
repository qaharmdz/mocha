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

namespace Mocha\System\Engine;

use Symfony\Component\HttpFoundation\ParameterBag;

class Presenter
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $param;

    public function __construct(ParameterBag $bag)
    {
        $this->param = $bag;

        $this->param->add([
            'debug'     => false,
            'timezone'  => 'UTC',
            'file_ext'  => '.html.twig',
            'global'    => [], // namespace 'mocha' at template file
            'theme'     => [
                'default'   => '',
                'active'    => ''
            ],
            'path'      => [
                'app'       => '',
                'theme'     => '',
                'cache'     => ''
            ]
        ]);
    }

    /**
     * Twig render.
     *
     * @param  string $template
     * @param  array  $vars
     *
     * @return string
     */
    public function render(string $template, array $vars = [])
    {
        if ($this->param->get('debug')) {
            $this->clearCache();
        }

        $template_path  = array_unique([
            $this->param->get('path.theme') . $this->param->get('theme.active') . DS . 'template' . DS,
            $this->param->get('path.theme') . $this->param->get('theme.default') . DS . 'template' . DS,
            $this->param->get('path.app'),
            PATH_MOCHA
        ]);

        if (!is_dir($template_path[0])) {
            unset($template_path[0]);
            throw new \InvalidArgumentException(sprintf('Theme "%s" is not available, fallback to theme "%s"', $this->param->get('theme.active'), $this->param->get('theme.default')));
        }

        $template   = str_replace('/\\', DS, $template . $this->param->get('file_ext'));
        $loader     = new \Twig_Loader_Filesystem($template_path);
        $twig       = new \Twig_Environment($loader, [
            'charset'           => 'utf-8',
            'autoescape'        => false,
            'debug'             => $this->param->get('debug'),
            'auto_reload'       => $this->param->get('debug'),
            'strict_variables'  => $this->param->get('debug'),
            'cache'             => $this->param->get('debug') ? false : $this->param->get('path.cache')
        ]);

        $twig->getExtension('Twig_Extension_Core')->setTimezone($this->param->get('timezone'));
        $twig->addExtension(new \Twig_Extension_StringLoader());        // {{ include(template_from_string("Hello {{ name }}")) }}

        if ($this->param->get('debug')) {
            $twig->addExtension(new \Twig_Extension_Debug());           // {{ dump(...) }}

            if (class_exists('Kint\Kint')) {
                $twig->addExtension(new \Kint\Twig\TwigExtension());    // {{ d(...) }}
            }
        }

        $twig->addGlobal('mocha', $this->param->get('global'));         // available in all templates and macros

        return $twig->render($template, $vars);
    }

    /**
     * Clear twig cache.
     *
     * @see https://github.com/twigphp/Twig/blob/cacfb069b2e65d5487489677238142b464135b40/lib/Twig/Environment.php#L502
     */
    public function clearCache()
    {
        $cache_path = $this->param->get('path.cache');

        if (file_exists($cache_path)) {
            foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cache_path),
                \RecursiveIteratorIterator::LEAVES_ONLY
            ) as $file) {
                if ($file->isFile()) {
                    @unlink($file->getPathname());
                }
            }
        }
    }
}
