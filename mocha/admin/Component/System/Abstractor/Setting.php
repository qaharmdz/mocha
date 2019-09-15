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

namespace Mocha\Admin\Component\System\Abstractor;

class Setting extends \Mocha\Abstractor
{
    public function getSettings(string $group, string $type)
    {
        $data    = [];
        $results = $this->db->run(
            'SELECT * FROM ' . DB_PREFIX . 'setting WHERE `group` = ? AND `type` = ?',
            [$group, $type]
        )->fetchAll();

        foreach ($results as $result) {
            $data[$result['key']] = $result['encoded'] ? json_decode($result['value'], true) : $result['value'];
        }

        return $data;
    }

    public function update(string $group, string $type, array $data)
    {
        $this->db->run('DELETE FROM ' . DB_PREFIX . 'setting WHERE `group` = ? AND `type` = ?', [$group, $type]);

        foreach ($data as $key => $value) {
            $columns = [
                'group'   => $group,
                'type'    => $type,
                'key'     => $key,
                'value'   => is_array($value) ? json_encode($value) : $value,
                'encoded' => is_array($value) ? 1 : 0,
            ];

            $this->db->run('INSERT INTO ' . DB_PREFIX . 'setting (`group`, `type`, `key`, `value`, `encoded`) VALUES (:group, :type, :key, :value, :encoded)', $columns);
        }
    }
}
