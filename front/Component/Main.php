<?php
namespace Mocha\Front\Component;

class Main extends \Mocha\Controller
{
    public function index()
    {
        $data      = [];
        $component = $this->dispatcher->handle($this->request);

        // Component direct output
        if ($component->hasOutput()) {
            return $component->getOutput();
        }

        /*
        Layout
        - Blank: No header, no footer, white canvas
        - Basic: Header, Footer
        - Sidebar: Basic + Sidebar
         */

        $data['component'] = $component->hasContent() ? $component->getContent() : 'No component content';

        return $this->response
                    ->setStatusCode($component->getStatusCode())
                    ->setContent($this->presenter->render('index', $data));
    }
}
