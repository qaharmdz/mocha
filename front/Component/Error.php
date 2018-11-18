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
        $this->session->flash->set('document_meta', [
            'title'      => '404 Not Found!',
            'body_class' => 'page-error page-404',
            'layout'     => 'blank'
        ]);
        return $this->response
            ->setStatusCode($exception->getStatusCode())
            ->setContent($this->presenter->render('error', ['content' => '<h1>404 Not Found!</h1> <p>' . $exception->getMessage() . '</p>']));
    }

    protected function serviceError($exception)
    {
        $this->session->flash->set('document_meta', [
            'title'      => $exception->getStatusCode() . ' Oops!',
            'body_class' => 'page-error page-500',
            'layout'     => 'blank'
        ]);

        return $this->response
            ->setStatusCode($exception->getStatusCode())
            ->setContent(
                '<h1>Oops, bad thing happen!</h1><p>Message: <i>' . $exception->getMessage() . '</i></p>'
            )
            ->setOutput();
    }
}
