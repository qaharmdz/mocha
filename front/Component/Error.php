<?php
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
                'content'  => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Labore adipisci quam voluptatem rerum veniam. Facilis tempora, id libero minima rem vel aliquam doloremque atque, eveniet nostrum perferendis hic, vero nulla.'
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
