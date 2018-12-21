<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
