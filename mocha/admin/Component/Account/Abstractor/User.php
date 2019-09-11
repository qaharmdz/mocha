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
    // public function getUsers()
    // {
    //     $data    = [];
    //     $results = $this->db->where('status', 'enabled')
    //                         ->get('user', null, ['user_id']);

    //     foreach ($results as $result) {
    //         $user_metas = $this->tool_abstract_meta->get('user', $result['user_id']);

    //         $data[$user_metas['firstname']] = array_merge($result, $user_metas);
    //     }

    //     ksort($data);
    // }

    public function getRecords($param)
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
        $filter_map['role_title'] = 'r.title';
        $filter_map['fullname']   = 'CONCAT_WS(" ", umFn.value, umLn.value, umDn.value)';

        return $this->db->join('role r', 'u.role_id = r.role_id', 'LEFT')
                        ->join('user_meta umFn', 'u.user_id = umFn.user_id AND umFn.attribute = "firstname"', 'LEFT')
                        ->join('user_meta umLn', 'u.user_id = umLn.user_id AND umLn.attribute = "lastname"', 'LEFT')
                        ->join('user_meta umDn', 'u.user_id = umDn.user_id AND umDn.attribute = "displayname"', 'LEFT')
                        ->groupBy('u.user_id')
                        ->get('user u', [0, 2], array_values($column_map));
    }
}
