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

namespace Mocha\Front\Component;

class Error extends \Mocha\Controller
{
    public function index($exception)
    {
        return $exception->getStatusCode() == 404 ? $this->notFound($exception) : $this->serviceError($exception);
    }

    protected function notFound($exception)
    {
        $this->document->setTitle('404 Not Found!');
        $this->document->addNode('class_body', ['page-error page-404']);

        return $this->response
            ->setStatusCode($exception->getStatusCode())
            ->setContent($this->presenter->render('error', [
                'title'    => '404 Not Found!',
                'subtitle' => $exception->getMessage(),
                'content'  => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.'
            ]));
    }

    protected function serviceError($exception)
    {
        $this->document->setTitle($exception->getStatusCode() . ' Oops!');
        $this->document->addNode('class_body', ['page-error page-500']);

        return $this->response
            ->setStatusCode($exception->getStatusCode())
            ->setContent('<h1>Oops, bad thing happen!</h1><p>Message: <i>' . $exception->getMessage() . '</i></p>')
            ->setOutput();
    }
}
