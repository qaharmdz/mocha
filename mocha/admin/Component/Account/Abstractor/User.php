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

namespace Mocha\Admin\Component\Account\Abstractor;

class User extends \Mocha\Abstractor
{
    // Record
    // ================================================

    public function getRecords(array $params)
    {
        $column_map = array(
            'user_id'       => 'u.user_id',
            'role_id'       => 'u.role_id',
            'role_title'    => 'r.title AS role_title',
            'fullname'      => 'CONCAT(umFn.value, " ", umLn.value) AS fullname',
            'displayname'   => 'umDn.value AS displayname',
            'email'         => 'u.email',
            'status'        => 'u.status',
            'created'       => 'u.created',
            'updated'       => 'u.updated',
            'last_login'    => 'u.last_login'
        );

        $filter_map = $column_map;
        $filter_map['role_title']  = 'r.title';
        $filter_map['fullname']    = 'CONCAT(umFn.value, " ", umLn.value)';
        $filter_map['displayname'] = 'umDn.value';

        $dataTables = $this->tool_datatables->parse($params)->getQuery($filter_map);

        $args  = [];
        $query = 'SELECT ' . implode(', ', array_values($column_map)) . '
            FROM ' . DB_PREFIX . 'user u
                LEFT JOIN ' . DB_PREFIX . 'role r ON (u.role_id = r.role_id)
                LEFT JOIN ' . DB_PREFIX . 'user_meta umFn ON (u.user_id = umFn.user_id AND umFn.attribute = "firstname")
                LEFT JOIN ' . DB_PREFIX . 'user_meta umLn ON (u.user_id = umLn.user_id AND umLn.attribute = "lastname")
                LEFT JOIN ' . DB_PREFIX . 'user_meta umDn ON (u.user_id = umDn.user_id AND umDn.attribute = "displayname")';

        if ($dataTables['search']['query']) {
            $query .= $dataTables['search']['query'];
            $args  += $dataTables['search']['vars'];
        }

        $query .= ' GROUP BY u.user_id';

        if ($dataTables['order']['query']) {
            $query .= $dataTables['order']['query'];
        }

        $query .= $dataTables['limit']['query'];
        $args  += $dataTables['limit']['vars'];

        return $this->db->run($query, $args)->fetchAll();
    }

    public function getTotalRecords()
    {
        return $this->db->run('SELECT count(*) FROM ' . DB_PREFIX . 'user')->fetchColumn();
    }

    // Form
    // ================================================
}
