<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\Tickets\Filters\Terms\FilterQuery;

class TicketQueryBuilder
{
    /**
     * @param array       $fields
     * @param FilterQuery $query
     *
     * @return array array('sql' => '...', 'params' => array(...))
     */
    public function getSelectQuery(array $fields, FilterQuery $query)
    {
        $parts = $query->getQueryParts();

        $query = 'SELECT '.implode(', ', $fields)."\n";
        $query .= "FROM tickets\n";

        $join_map = [];
        if (!empty($parts['joins'])) {
            $count = 0;
            foreach ($parts['joins'] as $join) {
                ++$count;
                $alias = $join['alias'] ? $join['alias'] : $join['input_alias'];
                $query .= "LEFT JOIN {$join['join']} AS $alias ON ({$join['condition']})\n";
                if ($join['alias']) {
                    $join_map[$join['alias']] = 'join'.$count;
                }
            }
        }
        $query .= "WHERE\n".$parts['where'];

        $param_map = [];
        foreach ($parts['params'] as $p) {
            $param_map[$p['name']] = $p['value'];
        }

        $ret_params = [];
        $query      = preg_replace_callback('#__dpparam_.*?__#', function ($m) use (&$ret_params, $param_map) {
            $ret_params[] = $param_map[$m[0]];

            return '?';
        }, $query);
        $query = preg_replace_callback('#__dpjoin_.*?__#', function ($m) use ($join_map) {
            return $join_map[$m[0]];
        }, $query);

        return [
            'sql'    => $query,
            'params' => $ret_params,
        ];
    }

    /**
     * @param FilterQuery $query
     *
     * @return array array('sql' => '...', 'params' => array(...))
     */
    public function getCountQuery(FilterQuery $query)
    {
        $parts = $query->getQueryParts();

        $query = "SELECT COUNT(*)\n";
        $query .= "FROM tickets\n";

        $join_map = [];
        if (!empty($parts['joins'])) {
            $count = 0;
            foreach ($parts['joins'] as $join) {
                ++$count;
                $alias = $join['alias'] ? $join['alias'] : $join['input_alias'];
                $query .= "LEFT JOIN {$join['join']} AS $alias ON ({$join['condition']})\n";
                if ($join['alias']) {
                    $join_map[$join['alias']] = 'join'.$count;
                }
            }
        }
        $query .= "WHERE\n".$parts['where'];

        $param_map = [];
        foreach ($parts['params'] as $p) {
            $param_map[$p['name']] = $p['value'];
        }

        $ret_params = [];
        $query      = preg_replace_callback('#__dpparam_.*?__#', function ($m) use (&$ret_params, $param_map) {
            $ret_params[] = $param_map[$m[0]];

            return '?';
        }, $query);
        $query = preg_replace_callback('#__dpjoin_.*?__#', function ($m) use ($join_map) {
            return $join_map[$m[0]];
        }, $query);

        return [
            'sql'    => $query,
            'params' => $ret_params,
        ];
    }
}
