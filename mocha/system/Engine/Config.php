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

/**
 * ParameterBag with dot-notation features.
 *
 * @see \Adbar\Dot https://github.com/adbario/php-dot-notation
 */
class Config extends ParameterBag
{
    /**
     * Adds parameters.
     *
     * @param array $parameters
     */
    public function add(array $parameters = [])
    {
        $this->parameters = array_replace_recursive($this->parameters, $parameters);
    }

    /**
     * Returns a parameter by name (dot-notation).
     *
     * @param string $key
     * @param mixed  $default
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
     * @param array|string $key
     * @param mixed  $value
     */
    public function set($key, $value = null)
    {
        $this->setDot($key, $value);
    }

    /**
     * Check if a given key or keys exists.
     *
     * @param array|string $keys
     *
     * @return bool
     */
    public function has($keys)
    {
        return $this->hasDot($keys);
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
     * @see \Adbar\Dot::get()
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function getDot(string $key, $default = null)
    {
        if ($this->exists($this->parameters, $key)) {
            return $this->parameters[$key];
        }

        if (strpos($key, '.') === false) {
            return $default;
        }

        $items = $this->parameters;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($items) || !$this->exists($items, $segment)) {
                return $default;
            }

            $items = &$items[$segment];
        }

        return $items;
    }

    /**
     * Sets a parameter by dot-notation keys.
     *
     * @see \Adbar\Dot::set()
     *
     * @param array|string  $keys
     * @param mixed         $value
     */
    public function setDot($keys, $value = null)
    {
        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $this->setDot($key, $value);
            }

            return null;
        }

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
     * Check if a given key or keys exists.
     *
     * @see \Adbar\Dot::has()
     *
     * @param  array|string $keys
     *
     * @return bool
     */
    public function hasDot($keys)
    {
        $keys = (array) $keys;

        if (!$this->parameters || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $items = $this->parameters;

            if ($this->exists($items, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (!is_array($items) || !$this->exists($items, $segment)) {
                    return false;
                }

                $items = $items[$segment];
            }
        }

        return true;
    }

    /**
     * Remove a parameter by dot-notation keys.
     *
     * @see \Adbar\Dot::delete()
     *
     * @param  array|string $keys
     */
    public function removeDot($keys)
    {
        $keys = (array) $keys;

        foreach ($keys as $key) {
            if ($this->exists($this->parameters, $key)) {
                unset($this->parameters[$key]);

                continue;
            }

            $items = &$this->parameters;
            $segments = explode('.', $key);
            $lastSegment = array_pop($segments);

            foreach ($segments as $segment) {
                if (!isset($items[$segment]) || !is_array($items[$segment])) {
                    continue 2;
                }

                $items = &$items[$segment];
            }

            unset($items[$lastSegment]);
        }
    }

    /**
     * Configuration loader.
     *
     * @param  string $file
     *
     * @return array
     */
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

    /**
     * Load array file configuration.
     *
     * @param  string $file
     *
     * @return array
     */
    protected function loadArray(string $file)
    {
        $bags = include $file;

        $this->add((array)$bags);

        return $bags;
    }

    /**
     * Load json file configuration.
     *
     * @param  string $file
     *
     * @return array
     */
    protected function loadJson(string $file)
    {
        $bags = json_decode(file_get_contents($file), true);

        $this->add((array)$bags);

        return $bags;
    }

    /**
     * Load and add .env content to $bags, $_ENV and $_SERVER.
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

    /**
     * Checks if the given key exists in the provided array.
     *
     * @param  array      $array Array to validate.
     * @param  int|string $key   The key to look for.
     *
     * @return bool
     */
    protected function exists($array, $key)
    {
        return array_key_exists($key, $array);
    }
}
