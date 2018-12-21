<?php
/*
 * This file is part of Mocha package.
 *
 * This program is a "free software" which mean freedom to use, modify and redistribute.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Released under GPL version 3 or any later version.
 * Full copyright and license see LICENSE file or visit https://www.gnu.org/licenses/gpl-3.0.en.html.
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
