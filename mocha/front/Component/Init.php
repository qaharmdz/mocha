<?php
/**
 * This file is part of Mocha.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Released under GPL version 3 or any later version.
 * Copyright and license see LICENSE file or https://www.gnu.org/licenses/gpl-3.0.en.html.
 */

namespace Mocha\Front\Component;

use Mocha\Controller;

class Init extends Controller
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
        $columns = [
            'group'   => 1,
            'type'    => 2,
            'key'     => 3,
            'value'   => 4,
            'encoded' => 5,
        ];

        // d(implode(', ', array_keys($columns)));
        // d(implode(', ', array_values($columns)));

        // d(MOCHA);
        // d($data);

        // d($this->db->prepare('SELECT * FROM ' . DB_PREFIX . 'setting')->execute()); // execute return boolean
        // d($this->db->run('SELECT * FROM ' . DB_PREFIX . 'setting')->fetch()); // get one row
        // d($this->db->run('SELECT * FROM ' . DB_PREFIX . 'setting s WHERE s.group = ?', ['setting'])->fetchAll()); // get all rows
        // d($this->db->run('SELECT count(*) FROM ' . DB_PREFIX . 'setting')->fetchColumn()); // getting the number of rows in the table
        // d($this->db->run('SELECT * FROM ' . DB_PREFIX . 'user')->fetchAll(\PDO::FETCH_UNIQUE)); // first column as array key
        // d($this->db->run('SELECT `type`, `key`, `value`, `encoded` FROM ' . DB_PREFIX . 'setting s WHERE s.group = ?', ['setting'])->fetchAll(\PDO::FETCH_GROUP)); // get all rows

        /*
        $users = $this->db->run(
            'SELECT u.user_id, u.role_id, u.email, u.password, u.verify_code, u.verify_type,
                    r.title AS role_name, u.created, u.updated, u.last_login
            FROM ' . DB_PREFIX . 'user u
            LEFT JOIN ' . DB_PREFIX . 'role r ON (r.role_id = u.role_id)
            WHERE u.email = ? AND u.status = "enabled" AND r.status = "enabled"',
            ['qahar.5010@gmail.com']
        )->fetch();
        $results = $this->db->run(
            'SELECT um.attribute, um.value, um.encoded
            FROM ' . DB_PREFIX . 'user_meta um
            WHERE um.user_id = ?',
            [$users['user_id']]
        )->fetchAll();
        $accesses = $this->db->run(
            'SELECT rr.`group`, rr.type, ra.permission
            FROM ' . DB_PREFIX . 'role_access ra
            LEFT JOIN ' . DB_PREFIX . 'role_resource rr ON (ra.resource_id = rr.resource_id)
            WHERE ra.role_id = ?',
            [2]
        )->fetchAll();
        $resources = $this->db->run('SELECT rr.`group`, rr.type, rr.scheme, rr.route FROM ' . DB_PREFIX . 'role_resource rr')->fetchAll();

        d($users);
        d($results);
        d($accesses);
        d($resources);
         */

        // d($this->event);
        // d($this->event->getEmitters());
        // d($this->container()->keys());
        // d($this->config->all());
        // d($this->presenter->param->get('global'));

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

        // Datatables search: Parse search string
        $str = 'foo bar "great OR cool AND world" name:"john OR doe" price:100~200 age:~40 weight:20~ date:2018-05-12~2018-06-17';
        // preprocess the cases where you have colon separated definitions with quotes
        $str = preg_replace('/(\w+)\:"(\w+)/', '"${1}:${2}', $str);
        $str = str_getcsv($str, ' ');
        // d($str);

        $column = [
            'keyword'  => 'global',
            'search'   => '',
            'operator' => ''
        ];
        $parts = [];
        for ($i=0; $i < count($str); $i++) {
            $part = [];
            $part['search'] = $str[$i];

            if (strpos($str[$i], ':') !== false) {
                $segments = explode(':', $str[$i]);
                $part['keyword'] = $segments[0];
                $part['search']  = $segments[1];
            }

            if (strpos($str[$i], '~') !== false) {
                $part['search'] = array_map('trim', explode('~', $part['search']));
                $part['operator'] = 'range';
            }

            if (!is_array($part['search']) &&strpos($str[$i], 'OR') !== false) {
                $part['search'] = array_map('trim', explode('OR', $part['search']));
                $part['operator'] = 'or';
            }

            if (!is_array($part['search']) && strpos($str[$i], 'AND') !== false) {
                $part['search'] = array_map('trim', explode('AND', $part['search']));
                $part['operator'] = 'and';
            }

            $parts[$i] = array_replace($column, $part);
        }
        // d($parts);
    }
}
