<?php
namespace Mocha\Front\Component\Home\Controller;

class Home extends \Mocha\Controller
{
    public function index()
    {
        $content = 'Home Component';

        return $this->response->setContent($content);
    }
}
