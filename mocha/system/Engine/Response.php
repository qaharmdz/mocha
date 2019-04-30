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
use Symfony\Component\HttpKernel\Exception\HttpException;

class Response extends HttpFoundation\Response
{
    /**
     * @var HttpFoundation\Response
     */
    private $output;

    /**
     * A layer for content indicate that $response must be send immediately.
     *
     * @param  HttpFoundation\Response $output
     *
     * @return $this  Response instance (render, redirect, json, file etc).
     */
    public function setOutput(HttpFoundation\Response $output = null)
    {
        $this->output = $output === null ? $this : $output;

        return $this;
    }

    /**
     * @return HttpFoundation\Response
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return boolean
     */
    public function hasOutput()
    {
        return $this->output ? true : false;
    }

    /**
     * Check if response has content
     *
     * @return bool
     */
    public function hasContent()
    {
        return $this->content ? true : false;
    }

    /**
     * Redirects to another URL.
     *
     * 301 Permanently redirect from old url to new url.
     * 302 Forwarding to new url (temporary redirect).
     * 303 In response to a POST, redirect to new url with GET method. Redirect after form submission.
     * 400 Bad request
     * 401 Unauthorized access
     * 403 Forbidden, in response to permission issue
     * 404 Not Found
     * 412 Precondition failed
     * 422 Unprocessable entity, in response to form validation or general error
     * 500 Internal server error
     *
     * @param  string $url     The URL should be a full URL, with schema etc.
     * @param  int    $status  The status code (302 by default).
     *
     * @return $this
     */
    public function redirect(string $url, int $status = 302)
    {
        $response = new HttpFoundation\RedirectResponse($url, $status);
        $response->setContent('');

        return $this->setOutput($response);
    }

    /**
     * Return a JSON response.
     *
     * @param  mixed $data    The response data.
     * @param  int   $status  The response status code.
     * @param  array $headers An array of response headers.
     *
     * @return $this
     */
    public function jsonOutput($data = [], int $status = 200, array $headers = [])
    {
        return $this->setOutput(new HttpFoundation\JsonResponse($data, $status, $headers));
    }

    /**
     * Send a file.
     *
     * @param  string $file     Path to file.
     * @param  string $mask     Mask filename.
     * @param  array  $headers
     *
     * @return $this
     */
    public function fileOutput(string $file, string $mask = '', array $headers = [])
    {
        $response = new HttpFoundation\BinaryFileResponse($file, 200, $headers, true);

        if ($mask) {
            $response->setContentDisposition(
                HTTPFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $mask
            );
        }

        return $this->setOutput($response);
    }

    /**
     * Aborts current request by sending a HTTP error.
     *
     * @param int    $statusCode The HTTP status code.
     * @param string $message    The status message.
     * @param array  $headers    An array of HTTP headers.
     *
     * @throws HttpException
     */
    public function abort(int $statusCode, string $message = null, array $headers = [])
    {
        $message = $message ?: parent::$statusTexts[$statusCode] ?? null;

        throw new HttpException($statusCode, $message, null, $headers);
    }
}
