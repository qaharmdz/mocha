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

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Extra dot-notation to HttpFoundation\ParameterBag
 */
class Config extends ParameterBag
{
    /**
     * Adds parameters.
     *
     * @param array $parameters An array of parameters
     */
    public function add(array $parameters = array())
    {
        $this->parameters = array_replace_recursive($this->parameters, $parameters);
    }

    /**
     * Returns a parameter by name (dot-notation).
     *
     * @param string $key     The key
     * @param mixed  $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->getDot($key, $default);
    }

    /**
     * Sets a parameter by name (dot-notation).
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function set($key, $value)
    {
        $this->setDot($key, $value);
    }

    /**
     * Override default remove to use dot-notation
     *
     * @param string $key Key in dot-notation
     */
    public function remove($key)
    {
        $this->removeDot($key);
    }

    /**
     * Returns the parameter value converted to array.
     *
     * @param  string $key     The parameter key
     * @param  array  $default The default value if the parameter key does not exist
     *
     * @return array
     */
    public function getArray(string $key, $default = [])
    {
        return (array)$this->get($key, $default);
    }

    /**
     * Returns a parameter by dot-notation keys.
     *
     * @param  string $key     Key in dot-notation
     * @param  mixed  $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function getDot(string $key, $default = null)
    {
        $items = $this->parameters;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($items) || !array_key_exists($segment, $items)) {
                return $default;
            }

            $items = &$items[$segment];
        }

        return $items;
    }

    /**
     * Sets a parameter by dot-notation keys.
     *
     * @param string $keys  Key in dot-notation
     * @param mixed  $value The value
     */
    public function setDot(string $keys, $value)
    {
        $items = &$this->parameters;

        foreach (explode('.', $keys) as $key) {
            if (!isset($items[$key]) || !is_array($items[$key])) {
                $items[$key] = [];
            }

            $items = &$items[$key];
        }

        $items = $value;
    }

    /**
     * Remove a parameter by dot-notation keys.
     *
     * @param  string $keys String key in dot-notation
     */
    public function removeDot(string $keys)
    {
        if (isset($this->parameters[$keys])) {
            unset($this->parameters[$keys]);
        } else {
            $items = &$this->parameters;
            $segments = explode('.', $keys);
            $lastSegment = array_pop($segments);

            foreach ($segments as $segment) {
                if (!isset($items[$segment]) || !is_array($items[$segment])) {
                    continue;
                }
                $items = &$items[$segment];
            }
            unset($items[$lastSegment]);
        }
    }

    public function load(string $file, string $type = 'array')
    {
        if (!is_file($file)) {
            throw new \RuntimeException(sprintf('File "%s" not found.', $file));
        }

        switch ($type) {
            case 'array':
                return $this->loadArray($file);
                break;

            case 'json':
                return $this->loadJson($file);
                break;

            case 'env':
                return $this->loadEnv($file);
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Config type "%s" not recognized.', $type));
                break;
        }
    }

    protected function loadArray(string $file)
    {
        $bags = include $file;

        $this->add((array)$bags);

        return $bags;
    }

    protected function loadJson(string $file)
    {
        $bags = json_decode(file_get_contents($file), true);

        $this->add((array)$bags);

        return $bags;
    }

    /**
     * Load .env file content to config, $_ENV and $_SERVER
     *
     * @param  string $file
     *
     * @return array
     */
    protected function loadEnv(string $file)
    {
        $bags = [];

        $getenv = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $dotenv = array_map(function ($v) {
            return explode('=', $v);
        }, (array)$getenv);

        foreach ($dotenv as $envs) {
            if (substr($envs[0], 0, 1) != '#') {
                list($name, $value) = array_map('trim', $envs);

                $bags[$name]    = $value;
                $_ENV[$name]    = $value;
                $_SERVER[$name] = $value;
                putenv($name . '=' . $value);
                $this->set($name, $value);
            }
        }

        return $bags;
    }
}
