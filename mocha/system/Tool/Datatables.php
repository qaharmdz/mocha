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

namespace Mocha\System\Tool;

use Mocha\Controller;

class Datatables extends Controller
{
    protected $data = [];

    /**
     * Parse datatables ajax request
     *
     * @param  array  $params   Request param
     * @param  array  $excludes Exclude column by key, negative from right
     *
     * @return array
     */
    public function parse(array $params, array $excludes = [0, -1])
    {
        $this->data['_request'] = $params;

        if (empty($params['draw'])) {
            return [];
        }

        $this->data['columns'] = $params['columns'];
        $this->data['search']  = [
            'all'     => '',
            'columns' => []
        ];

        // Remove unused column; preserve key (column sequence)
        $count_cols = count($params['columns']);
        foreach ($excludes as $key) {
            $key = ($key < 0) ? ($count_cols + $key) : $key;
            unset($this->data['columns'][$key]);
        }

        // Parse build
        if ($params['search']['value']) {
            $this->data['search']['all'] = $params['search']['value'];
        }

        foreach ($this->data['columns'] as $key => $value) {
            $this->data['columns'][$key] = $value['data'];

            if ($value['search']['value']) {
                $this->data['search']['columns'][$value['data']] = $value['search']['value'];
            }
        }

        foreach ($params['order'] as $order) {
            $this->data['order'][$this->data['columns'][(int)$order['column']]] = $order['dir'] == 'asc' ? 'asc' : 'desc';
        }

        $this->data['limit'] = [
            'start'  => (int)$params['start'],
            'length' => (int)$params['length']
        ];

        return $this;
    }

    /**
     * Return parsed $data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return SQL query based on parsed $data
     *
     * @return array
     */
    public function getQuery(array $filter_map, array $date_map = [])
    {
        $output   = [];
        $data     = $this->data;
        $date_map = array_unique(array_merge(['created', 'updated', 'publish', 'unpublish', 'last_login'], $date_map));

        if (!$data) {
            return $output;
        }

        $output['search'] = ['query' => '', 'vars'  => []];
        if ($data['search']) {
            $search = ['query' => [],'vars'  => []];

            if ($data['search']['all']) {
                $search_all = [];
                foreach ($filter_map as $filter_key => $column) {
                    if (!in_array($filter_key, $date_map)) {
                        $search_all[] = $column . ' LIKE :search_all_' . $filter_key;
                        $search['vars']['search_all_' . $filter_key] = '%' . $data['search']['all'] . '%';
                    }
                }

                if ($search_all) {
                    $search['query'][] = '(' . implode(' OR ', $search_all) . ')';
                }
            }

            if ($data['search']['columns']) {
                # code...
            }

            if ($search['query']) {
                $output['search']['query'] .= ' WHERE ' . implode(' AND ', $search['query']);
                $output['search']['vars']  += $search['vars'];
            }
        }

        $output['order'] = ['query' => '', 'vars'  => []];
        if ($data['search']) {
            $orders = [];

            foreach ($data['order'] as $key => $value) {
                if (isset($filter_map[$key])) {
                    $orders[] = $filter_map[$key] . ' ' . $value;
                }
            }

            if ($orders) {
                $output['order']['query'] = ' ORDER BY ' . implode(', ', $orders);
            }
        }

        $output['limit'] = [
            'query' => ' LIMIT :limit_start, :limit_length',
            'vars'  => [
                'limit_start'  => $data['limit']['start'],
                'limit_length' => $data['limit']['length']
            ]
        ];

        return $output;
    }
}
