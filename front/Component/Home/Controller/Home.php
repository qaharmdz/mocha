<?php
namespace Mocha\Front\Component\Home\Controller;

class Home extends \Mocha\Controller
{
    public function index()
    {
        $this->document->setTitle('404 Not Found!');
        $this->document->addNode('class_body', ['page-home']);

        $content = 'Home Component';

        return $this->response->setContent($content);
    }
}
