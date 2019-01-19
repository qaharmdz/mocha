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

namespace Mocha\Front\Component;

class Init extends \Mocha\Controller
{
    public function index()
    {
        $data = $this->event->trigger('init.start', $this->language->all())->getData();
        // TODO: plugin from url alias to route $this->request->setPathInfo('/home/test'); // sample to manipulate requested component

        $this->document->addNode('class_html', ['theme-' . $this->config->get('setting.site.theme_front')]);

        // ========= Content

        // === Component

        /**
         * Component event middleware, see:
         * - \Symfony\Component\HttpKernel\KernelEvents
         * - \Symfony\Component\HttpKernel\HttpKernel
         *
         * @return \Mocha\System\Engine\Response $component
         */
        $component = $this->event->trigger('init.component', [], $this->dispatcher->handle($this->request))->getOutput();

        if ($component->hasOutput()) {
            /**
             * @return \Mocha\System\Engine\Response
             */
            return $this->event->trigger('init.component.output', [], $component->getOutput());
        }

        $data['component'] = $component->hasContent() ? $component->getContent() : 'Content is not available.';

        // === Block Layouts

        // Direct access controller, without event middleware
        /*
        TODO: at parent controller
            - $this->controller() load controller
            - $this->model(name, object) register model
            - $this->model(name, method) call model method wrapped in event

        d($this->controllerResolver->resolve('home', []));
        d($this->controllerResolver->resolve('cool/app', [], 'module'));
         */

        // ========= Presenter

        /**
         * template_base
         * - index   : Header, Footer, Block Layout
         * - minimum : Header, Footer, no Block Layout
         * - blank   : No Header, no Footer, no Block Layout
         */
        $template = $this->document->getNode('template_base', 'index');

        $this->presenter->param->add(
            $this->event->trigger(
                'init.twig.global',
                ['global' => [
                    'data'      => $data,
                    'theme'     => $this->tool->metafile('theme', $this->config->get('setting.site.theme')),
                    'config'    => $this->config,
                    'router'    => $this->router,
                    'document'  => $this->document,
                    'i18n'      => $this->language,
                    'request'   => [
                        'method'    => $this->request->getRealMethod(),
                        'secure'    => $this->request->isSecure(),
                        'post'      => $this->request->post,
                        'query'     => $this->request->query, // $_GET
                        'cookies'   => $this->request->cookies
                    ],
                    'tool_secure'   => $this->tool_secure
                ]]
            )->getData()
        );

        $this->test($data);

        return $this->response
            ->setStatusCode($component->getStatusCode())
            ->setContent($this->tool->render(
                $template,
                $data,
                'init.' . $template
            ));
    }

    protected function test($data = [])
    {
        // d(MOCHA);
        // d($data);

        // d($this->event);
        // d($this->event->getEmitters());
        // d($this->container()->keys());
        d($this->config->all());
        d($this->presenter->param->get('global'));

        // d($this->session->all());
        // $this->session->set('foo', 'bar');

        // !d(
        //     $this->secure->generateCode('hash'),
        //     $this->secure->generateCode('hash', 21),
        // );

        // d(
        //     $this->request->attributes->all(),
        //     $this->request->query->all()
        // );
        // d();

        // d('==============================');
        // d($this->user->login('admin@example.com', 'password'));
        // d($this->user->isLogged());
        // d($this->session->all());

        // d($this->user->logout());
        // d($this->user->isLogged());
        // d($this->session->all());

        // d('==============================');
        // d($this->user->login('admin@example.com', 'password'));
        // d($this->user->isLogged());
        // d($this->session->all());
        // d($this->user->logout());

        // d(
            // $this->date->param->all(),
            // $this->date->carbon->getSettings(),
            // $this->date->carbon
            // ''
        // );
        // !d([
        //     ['info' => 'now UTC f-dts', 'carbon' => $nowUTC = $this->date->now('dts', 'utc')],
        //     ['info' => 'now user f-dtf', 'carbon' => $nowUser = $this->date->now()],
        //     ['info' => 'now user f-atom', 'carbon' => $this->date->now(DATE_ATOM)],
        //     ['info' => 'now user +7d', 'carbon' => $this->date->now('dtf', null, '+7 days 3 hours')],
        //     ['info' => 'now user -1w', 'carbon' => $this->date->now('dtf', null, '-1 weeks')],
        //     ['info' => 'now user -1w', 'carbon' => $this->date->now('dtf', null, '-1 weeks')],
        //     ['info' => 'shift utc to user', 'carbon' => $this->date->shift($nowUTC)],
        //     ['info' => 'shift utc to user', 'carbon' => $this->date->shift($nowUTC, ['diffHuman' => true])],
        //     ['info' => 'shift user to utc', 'carbon' => $this->date->shift($nowUser, ['from_tz' => 'user', 'from_format' => 'dtf', 'to_tz' => 'utc', 'to_format' => 'dts'])],
        //     ['info' => 'shift user to utc', 'carbon' => $this->date->shift($nowUser, ['from_tz' => 'user', 'from_format' => 'dtf', 'to_tz' => 'utc', 'to_format' => 'dts', 'diffHuman' => true])],
        //     ['info' => 'shiftUsertoUTC()', 'carbon' => $this->date->shiftUsertoUTC($nowUser)],
        //     ['info' => 'gmtOffset()', 'carbon' => $this->date->gmtOffset()],
        //     ['info' => 'gmtOffset()', 'carbon' => $this->date->gmtOffset('America/New_York')],
        //     ['info' => 'toSqlFormat()', 'carbon' => $this->date->toSqlFormat()],
        //     ['info' => 'tojQueryUIFormat()', 'carbon' => $this->date->tojQueryUIFormat()],
        // ]);
    }
}
