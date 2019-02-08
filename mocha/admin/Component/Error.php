<?php
/*
 * This file is part of Mocha package.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Released under GPL version 3 or any later version.
 * Full copyright and license see LICENSE file or visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 */

namespace Mocha\Admin\Component;

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
            ->setContent($this->tool->render('error', [
                'title'    => '404 Not Found!',
                'subtitle' => 'Unable to find the controller for path "' . $this->request->attributes->get('_controller') . '".',
                'content'  => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.'
            ]));
    }

    protected function serviceError($exception)
    {
        $this->document->setTitle($exception->getStatusCode() . ' Oops!');
        $this->document->addNode('class_body', ['page-error page-500']);

        return $this->response
            ->setStatusCode($exception->getStatusCode())
            ->setContent(
                '<h1>Oops, bad thing happen!</h1>' .
                '<p>' . $exception->getMessage() . '</p>' .
                '<p>At <i>' . str_replace(PATH_ROOT, '', $exception->getFile()) . '</i> line ' . $exception->getLine() . '.</p>'
            )
            ->setOutput();
    }
}
