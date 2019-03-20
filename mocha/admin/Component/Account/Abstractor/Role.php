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

class Role extends \Mocha\Abstractor
{
    public function getRoles()
    {
        return $this->db->where('status', 'enabled')
                            ->orderBy('role_id', 'ASC')
                            ->get('role', null, ['role_id', 'title', 'locked']);
    }

    public function getRoleResourcesRoute()
    {
        return $this->db->getValue('role_resource', 'route', null);
    }
}
