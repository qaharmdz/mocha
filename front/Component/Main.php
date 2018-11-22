<?php
namespace Mocha\Front\Component;

class Main extends \Mocha\Controller
{
    public function index(array $data = [])
    {
        $this->document->setTitle('Mocha - Pragmatic Content Management');
        $this->document->addNode('class_html', ['theme-' . $this->config->get('setting.site.theme_front')]);

        // ====== Component

        $component = $this->dispatcher->handle($this->request);

        if ($component->hasOutput()) {
            return $component->getOutput();
        }

        $data['component'] = $component->hasContent() ? $component->getContent() : 'No component content';

        // Module Positions



        $this->presenter->param->add(['global' => [
            'config'    => $this->config,
            'document'  => $this->document,
        ]]);

        // d($this->presenter->param->get('global'));

        return $this->response
                    ->setStatusCode($component->getStatusCode())
                    ->setContent($this->presenter->render(
                        $this->document->getNode('layout', 'index'),
                        $data
                    ));
    }
}

/*
Layout
- Blank   : No header, no footer, white canvas
- Minimum : Header, Footer
- Full    : Basic + Sidebar
 */
