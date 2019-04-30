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

use Symfony\Component\HttpFoundation;

class Request extends HttpFoundation\Request
{
    /**
     * Alias of parent::request ($_POST) properties.
     */
    public $post;

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);

        $this->post = $this->request;
    }

    /**
     * Consistenly remove trailing slash from PathInfo.
     *
     * @return string The raw path (not urldecoded).
     */
    public function getPathInfo()
    {
        return rtrim(parent::getPathInfo(), '/') ?: '/';
    }

    /**
     * Set pathinfo.
     *
     * @return string The raw path (not urldecoded).
     */
    public function setPathInfo(string $path)
    {
        $this->pathInfo = $path;
    }

    /**
     * Get base URI for the Request.
     *
     * @return string Base URI.
     */
    public function getBaseUri()
    {
        return $this->getSchemeAndHttpHost() . $this->getBasePath() . '/';
    }

    /**
     * Generates URI for the given path.
     *
     * @param  string $path
     *
     * @return string
     */
    public function getUriForPath($path)
    {
        return $this->getBaseUri() . ltrim($path, '/');
    }

    /**
     * Change the sequence in parent::get() to attributes, post, query ($_GET).
     *
     * @param  string $key
     * @param  mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this !== $result = $this->attributes->get($key, $this)) {
            return $result;
        }

        if ($this !== $result = $this->post->get($key, $this)) {
            return $result;
        }

        if ($this !== $result = $this->query->get($key, $this)) {
            return $result;
        }

        return $default;
    }

    /**
     * Check request type
     *
     * @param  string|array $check
     *
     * @return boolean
     */
    public function is($type)
    {
        if (is_array($type)) {
            $valid = true;
            foreach ($type as $check) {
                $valid = $this->is($check);
                if (!$valid) { break; }
            }

            return $valid;
        }

        switch (strtolower($type)) {
            case 'post':
                return $this->getMethod() == 'POST';

            case 'get':
                return $this->getMethod() == 'GET';

            case 'put':
                return $this->getMethod() == 'PUT';

            case 'delete':
                return $this->getMethod() == 'DELETE';

            case 'ssl':
            case 'https':
            case 'secure':
                return $this->isSecure();

            case 'ajax':
                return $this->isXmlHttpRequest();

            case 'cli':
                return (PHP_SAPI === 'cli' or defined('STDIN'));

            default:
                return false;
        }
    }
}
