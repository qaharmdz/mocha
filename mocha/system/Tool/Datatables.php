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
    protected $separator = '~';

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
        if (empty($params['draw'])) {
            return [];
        }

        $this->data['_request'] = $params;
        $this->data['columns']  = $params['columns'];
        $this->data['search']   = [
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
            $this->data['search']['all'] = trim($params['search']['value']);
        }

        foreach ($this->data['columns'] as $key => $column) {
            $this->data['columns'][$key] = $column['data'];

            $column_search = [
                'value'    => trim($column['search']['value']),
                'type'     => $params['params']['search'][$key]['type'] ?? 'text',
                'operator' => $params['params']['search'][$key]['operator'] ?? '%like%', // equal, range, %like%
                'negate'   => false
            ];

            if (in_array($column_search['value'], ['', '!=', $this->separator])) {
                continue;
            }

            if (in_array($column_search['type'], ['number', 'select'])) {
                $column_search['operator'] = 'equal';
            }

            if (substr($column_search['value'], 0, 2) === '!=') {
                $column_search['value']  = substr($column_search['value'], 2);
                $column_search['negate'] = true;
            }

            if ($column_search['type'] == 'number') {
                $column_search['value'] = (int)$column_search['value'];
            }

            if (strpos($column_search['type'], 'range') !== false) {
                $range = explode($this->separator, $column_search['value']);
                $column_search['value'] = $range;
                $column_search['operator'] = 'range';

                if ($column_search['type'] == 'number-range') {
                    $column_search['value'] = [
                        isset($range[0]) ? (int)$range[0] : null,
                        isset($range[1]) ? (int)$range[0] : null
                    ];
                } elseif ($column_search['type'] == 'date-range') {
                    $column_search['value'] = [
                        !empty($range[0]) ? $this->date->shiftToUTC($range[0], ['from_format' => 'df', 'reset' => true]) : null,
                        !empty($range[1]) ? $this->date->shiftToUTC($range[1], ['from_format' => 'df', 'reset' => true]) : null
                    ];
                }
            }

            $this->data['search']['columns'][$column['data']] = $column_search;
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
                foreach ($filter_map as $filter_key => $db_column) {
                    if (!in_array($filter_key, $date_map)) {
                        $search_all[] = $db_column . ' LIKE :search_all_' . $filter_key;
                        $search['vars']['search_all_' . $filter_key] = '%' . $data['search']['all'] . '%';
                    }
                }

                if ($search_all) {
                    $search['query'][] = '(' . implode(' OR ', $search_all) . ')';
                }
            }

            if ($data['search']['columns']) {
                foreach ($data['search']['columns'] as $filter_key => $filter) {
                    $output['debug'][$filter_key] = $filter;

                    switch ($filter['operator']) {
                        case 'equal':
                            $search['query'][] = $filter_map[$filter_key] . ' ' . ($filter['negate'] ? '!=' : '=') . ' :search_' . $filter_key;
                            $search['vars']['search_' . $filter_key] = $filter['value'];
                            break;

                        case 'range':
                            if ($filter['type'] == 'number-range') {
                                if (!empty($filter['value'][0]) && empty($filter['value'][1])) {
                                    $search['query'][] = $filter_map[$filter_key] . ' >= :search_' . $filter_key . '_0';
                                    $search['vars']['search_' . $filter_key . '_0'] = $filter['value'][0];
                                }
                                if (empty($filter['value'][0]) && !empty($filter['value'][1])) {
                                    $search['query'][] = $filter_map[$filter_key] . ' <= :search_' . $filter_key . '_1';
                                    $search['vars']['search_' . $filter_key . '_1'] = $filter['value'][1];
                                }
                                if (!empty($filter['value'][0]) && !empty($filter['value'][1])) {
                                    $search['query'][] = '(' . $filter_map[$filter_key] . ' BETWEEN :search_' . $filter_key . '_1 AND :search_' . $filter_key . '_0)';
                                    $search['vars']['search_' . $filter_key . '_0'] = $filter['value'][0];
                                    $search['vars']['search_' . $filter_key . '_1'] = $filter['value'][1];
                                }
                            }

                            if ($filter['type'] == 'date-range' && in_array($filter_key, $date_map)) {
                                if (!empty($filter['value'][0]) && empty($filter['value'][1])) {
                                    $search['query'][] = $filter_map[$filter_key] . ' >= :search_' . $filter_key . '_0';
                                    $search['vars']['search_' . $filter_key . '_0'] = $filter['value'][0];
                                }
                                if (empty($filter['value'][0]) && !empty($filter['value'][1])) {
                                    $search['query'][] = $filter_map[$filter_key] . ' <= DATE_ADD(:search_' . $filter_key . '_1, INTERVAL 1 DAY)';
                                    $search['vars']['search_' . $filter_key . '_1'] = $filter['value'][1];
                                }
                                if (!empty($filter['value'][0]) && !empty($filter['value'][1])) {
                                    $search['query'][] = '(' . $filter_map[$filter_key] . ' BETWEEN :search_' . $filter_key . '_0 AND DATE_ADD(:search_' . $filter_key . '_1, INTERVAL 1 DAY))';
                                    $search['vars']['search_' . $filter_key . '_0'] = $filter['value'][0];
                                    $search['vars']['search_' . $filter_key . '_1'] = $filter['value'][1];
                                }
                            }
                            break;

                        default: // %like%
                            $search['query'][] = $filter_map[$filter_key] . ' ' . ($filter['negate'] ? 'NOT LIKE' : 'LIKE') . ' :search_' . $filter_key;
                            $search['vars']['search_' . $filter_key] = '%' . $filter['value'] . '%';
                            break;
                    }
                }
            }

            if ($search['query']) {
                $output['search']['query'] .= ' WHERE ' . implode(' AND ', $search['query']);
                $output['search']['vars']  += $search['vars'];
            }
        }

        $output['order'] = ['query' => '', 'vars'  => []];
        if ($data['order']) {
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
