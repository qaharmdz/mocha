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

namespace Mocha\Admin\Component\Account\Abstractor;

class User extends \Mocha\Abstractor
{
    public function getUsers()
    {
        $results = $this->db->where('status', 'enabled')
                            ->get('user', null, ['user_id']);

        foreach ($results as $result) {
            $user_metas = $this->tool_abstract_meta->get('user', $result['user_id']);

            $data[$user_metas['firstname']] = array_merge($result, $user_metas);
        }
        ksort($data);
    }
}
