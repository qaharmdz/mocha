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

namespace Mocha\System\Library;

use Mocha\System\Engine;

class User
{
    protected $data = [];
    protected $access = [];

    /**
     * @var \Mysqlidb
     */
    protected $db;

    /**
     * @var \Mocha\System\Library\Secure
     */
    protected $secure;

    /**
     * @var \Mocha\System\Engine\Session
     */
    protected $session;

    public function __construct(\Mysqlidb $db, Secure $secure, Engine\Session $session)
    {
        $this->db       = $db;
        $this->secure   = $secure;
        $this->session  = $session;

        if ($this->session->get('user_mail')) {
            $user = $this->dbGetUserByMail($this->session->get('user_mail'));

            if ($user['user_id']) {
                unset($user['password']);

                $this->dbGetRoleAccess($user['role_id']);

                $this->data = $user;
            } else {
                $this->logout();
            }
        }
    }

    public function login($email, $password)
    {
        $user = $this->dbGetUserByMail($email);

        if ($user['user_id'] && $this->secure->isValidPassword($password, $user['password'])) {
            if ($this->secure->isPasswordNeedRehash($user['password'])) {
                $this->dbUpdateHash($user['user_id'], $password);
            }
            unset($user['password']);

            $this->dbGetRoleAccess($user['role_id']);
            $this->dbUpdateLastLogin($user['user_id']);

            $this->data = $user;

            $this->session->migrate(true);
            $this->session->set('token', $this->secure->randCode('hash', 16));
            $this->session->set('user_mail', $user['email']);
            $this->session->set('user_activity', time());
            $this->session->set('fingerprint', $this->fingerprint());

            return true;
        }

        return false;
    }

    public function logout()
    {
        $this->data   = [];
        $this->access = [];

        $this->session->migrate(true);
        $this->session->remove('token');
        $this->session->remove('user_mail');
        $this->session->remove('fingerprint');
        $this->session->remove('user_activity');
    }

    public function isLogged()
    {
        return (bool)$this->get('user_id') ?: false;
    }

    public function isSuperAdmin()
    {
        return (bool)$this->access['super_admin'] ?? false;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $value)
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = $value;
        }
    }

    public function all()
    {
        return [
            'data'   => $this->data,
            'access' => $this->access
        ];
    }

    public function hasAccess(string $access, string $route = '')
    {
        $valid = $this->isSuperAdmin();

        if (!$valid) {
            if ($route) {
                $valid = !empty($this->access[$route][$access]);
            } else {
                $valid = !empty($this->access[$access]) ? $this->access[$access] : false;
            }
        }

        return $valid;
    }

    public function fingerprint()
    {
        return md5(json_encode([
            $this->get('user_id'),
            $this->get('email'),
            $this->getIp(),
            $_SERVER['HTTP_USER_AGENT'],
            $_SERVER['HTTP_ACCEPT_LANGUAGE'],
        ]));
    }

    public function getIp()
    {
        return getenv('HTTP_CLIENT_IP')?:
               getenv('HTTP_X_FORWARDED_FOR')?:
               getenv('HTTP_X_FORWARDED')?:
               getenv('HTTP_FORWARDED_FOR')?:
               getenv('HTTP_FORWARDED')?:
               getenv('REMOTE_ADDR');
    }

    protected function dbUpdateHash($id, $password)
    {
        return $this->db->where('user_id', $id)
                        ->update('user', ['password' => $this->secure->password($post['password'])]);
    }

    protected function dbUpdateLastLogin($id)
    {
        $utc = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->db->where('user_id', $id)->update('user', ['last_login' => $utc->format('Y-m-d H:i:s')]);
    }

    protected function dbGetUserByMail($email)
    {
        $column = [
            'u.user_id',
            'u.role_id',
            'u.email',
            'u.password',
            'u.verify_code',
            'u.verify_type',
            'r.title AS role_name',
            'u.created',
            'u.updated',
            'u.last_login'
        ];

        $users = $this->db->join('role r', 'u.role_id = r.role_id', 'LEFT')
                          ->where('u.email', $email)
                          ->where('u.status', 'enabled')
                          ->where('r.status', 'enabled')
                          ->getOne('user u', $column);

        $metas   = [];
        $results = $this->db->where('user_id', $users['user_id'])->get('user_meta');
        foreach ($results as $result) {
            $metas[$result['attribute']] = $result['serialized'] ? json_decode($result['value'], true) : $result['value'];
        }

        return !empty($users) ? array_merge($users, $metas) : null;
    }

    protected function dbGetRoleAccess($id)
    {
        $data   = [];

        // Main access
        $result = $this->db->where('role_id', $id)->getOne('role');

        $data['admin_access'] = (bool)$result['admin_access'];
        $data['super_admin']  = (bool)$result['super_admin'];

        // Access
        $accesses = $this->db->join('role_resource rr', 'ra.resource_id = rr.resource_id', 'LEFT')
                             ->where('ra.role_id', $id)
                             ->get('role_access ra', null, ['rr.`group`', 'rr.type', 'ra.permission']);

        $access_temp = [];
        foreach ($accesses as $access) {
            $access_temp[$access['group'] . '_' . $access['type']] = json_decode($access['permission'], true);
        }
        $access_temp = array_map(
            function ($x) {
                foreach ($x as $k => $v) {
                    $x[$k] = (bool)$v;
                };
                return $x;
            },
            $access_temp
        );

        // Resources
        $resources = $this->db->get('role_resource', null, ['`group`', 'type', 'scheme', 'route']);

        foreach ($resources as $resource) {
            $resource_scheme    = array_fill_keys(array_values(json_decode($resource['scheme'], true)), $data['super_admin']);
            $access_permission  = $access_temp[$resource['group'] . '_' . $resource['type']] ?? [];

            $data[$resource['route']] = array_merge($resource_scheme, $access_permission);
        }

        $this->access = $data;
    }
}
