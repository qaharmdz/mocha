<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mocha\System\Engine;

class Presenter
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $param;

    public function __construct(\Symfony\Component\HttpFoundation\ParameterBag $bag)
    {
        $this->param = $bag;

        $this->param->add([
            'debug'     => false,
            'timezone'  => 'UTC',
            'file_ext'  => '.html.twig',
            'global'    => [],
            'theme'     => [
                'default'   => 'base',
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
     * Twig render
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
            $this->param->get('path.theme') . $this->param->get('theme.active') . DS,
            $this->param->get('path.theme') . $this->param->get('theme.default') . DS,
            $this->param->get('path.app'),
            ROOT
        ]);

        if (!is_dir($template_path[0])) {
            unset($template_path[0]);

            throw new \InvalidArgumentException(sprintf('Theme "%s" is not available, fallback to theme "%s"', $this->param->get('theme.active'), $this->param->get('theme.default')));
        }

        $template   = $this->templateMap($template);
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
        $twig->addExtension(new \Twig_Extension_StringLoader());    // {{ include(template_from_string("Hello {{ name }}")) }}
        if ($this->param->get('debug')) {
            $twig->addExtension(new \Twig_Extension_Debug());       // {{ dump(...) }}
        }

        $twig->addGlobal('mocha', $this->param->get('global'));       // available in all templates and macros

        return $twig->render($template, $vars);
    }

    public function templateMap($template)
    {
        if (is_array($template) && !empty($template['default'])) {
            $file = $template['default'];

            if (!empty($template['active'])) {
                $file = $this->templateMap($template['active']);
            }

            if (!is_file($this->param->get('path.theme') . $this->param->get('theme.active') . DS . $file)
                && !is_file($this->param->get('path.theme') . $this->param->get('theme.default') . DS . $file)
            ) {
                $file = $this->templateMap($template['default']);
            }

            return $file;
        }

        return str_replace('/', DS, 'template/' . $template . $this->param->get('file_ext'));
    }

    /**
     * Clear twig cache
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
