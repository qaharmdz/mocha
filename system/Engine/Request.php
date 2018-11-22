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
     * Consistenly remove trailing slash from PathInfo
     *
     * @return string The raw path (not urldecoded)
     */
    public function getPathInfo()
    {
        return rtrim(parent::getPathInfo(), '/') ?: '/';
    }

    /**
     * Get base URI for the Request.
     *
     * @return string Base URI
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
}
