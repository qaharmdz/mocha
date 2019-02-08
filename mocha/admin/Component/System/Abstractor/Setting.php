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

namespace Mocha\Admin\Component\System\Abstractor;

class Setting extends \Mocha\Abstractor
{
    public function getSettings(string $group, string $type)
    {
        $data   = [];
        $results = $this->db->where('`group`', $group)
                            ->where('type', $type)
                            ->get('setting');

        foreach ($results as $result) {
            $data[$result['key']] = $result['encoded'] ? json_decode($result['value'], true) : $result['value'];
        }

        return $data;
    }

    public function getAliasType()
    {
        $results = $this->db->where('`group`', 'alias_type')
                            ->orderBy('`key`', 'DESC')
                            ->orderBy('`value`', 'ASC')
                            ->get('setting', null, ['`key`', '`value`']);

        $output = [];
        foreach ($results as $result) {
            $output[$result['key']][] = $result['value'];
        }

        return $output;
    }
}
