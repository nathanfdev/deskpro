<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Orb\Util\Arrays;
use Orb\Util\Util;

class FilterQuery
{
    /**
     * @var array
     */
    private $joins = [];

    /**
     * @var array
     */
    private $wheres_and = [];

    /**
     * @var array
     */
    private $wheres_or = [];

    /**
     * @var array
     */
    private $params = [];

    /**
     * Array of string replacements to do on the resulting query parts.
     *
     * @var array
     */
    private $var_renamed = [];

    /**
     * Appends a unique string to the end of a name to make it unique.
     * Eg: "name_param" becomes "name_param_dp_a".
     *
     * @param string $base
     * @param string $type
     *
     * @return string
     */
    private function getUniqueName($base, $type)
    {
        static $count = 0;
        ++$count;

        return "__dp{$type}_{$base}_".Util::baseEncode($count, 'letters').'__';
    }

    /**
     * @param string $fromAlias
     * @param string $join
     * @param string $alias
     * @param null   $condition
     */
    public function addJoin($fromAlias, $join, $alias, $condition = null)
    {
        $m           = null;
        $input_alias = $alias;
        $alias       = null;
        if (preg_match('#unique:([0-9a-zA-Z_]+])#', $alias, $m)) {
            $alias                                       = $this->getUniqueName($m[1], 'join');
            $this->var_renamed["{table.{$input_alias}}"] = $alias;
        }

        $this->joins[$input_alias] = [
            'fromAlias'   => $fromAlias,
            'join'        => $join,
            'input_alias' => $input_alias,
            'alias'       => $alias,
            'condition'   => $condition,
        ];
    }

    /**
     * @param string $where
     */
    public function andWhere($where)
    {
        $this->wheres_and[] = $where;
    }

    /**
     * Generates the proper 'where in(?,?,?)' code.
     *
     * @param $field_name
     * @param array $params
     * @param bool  $not
     */
    public function andWhereIn($field_name, array $params, $not = false)
    {
        if (!$params) {
            if ($not) {
                $this->andWhere('1');
            } else {
                $this->andWhere('0');
            }

            return;
        }

        $names = [];
        foreach (array_values($params) as $k => $p) {
            $names[] = "{param.in$k}";
            $this->setParameter("in$k", $p);
        }

        $not_str = $not ? 'NOT ' : '';
        $this->andWhere("$field_name {$not_str}IN (".implode(',', $names).')');
    }

    /**
     * Generates the proper 'where in(?,?,?)' code.
     *
     * @param $field_name
     * @param array $params
     * @param bool  $not
     */
    public function orWhereIn($field_name, array $params, $not = false)
    {
        if (!$params) {
            if ($not) {
                $this->andWhere('1');
            } else {
                $this->andWhere('0');
            }

            return;
        }

        $names = [];
        foreach (array_values($params) as $k => $p) {
            $names[] = "{param.in$k}";
            $this->setParameter("in$k", $p);
        }

        $not_str = $not ? 'NOT ' : '';
        $this->orWhere("$field_name {$not_str}IN (".implode(',', $names).')');
    }

    /**
     * @param string $where
     */
    public function orWhere($where)
    {
        $this->wheres_or[] = $where;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @param null   $type
     */
    public function setParameter($name, $value, $type = null, $rename = true)
    {
        $input_name = $name;

        if ($rename) {
            $name                                       = $this->getUniqueName($input_name, 'param');
            $this->var_renamed["{param.{$input_name}}"] = $name;
        }

        $this->params[$name] = [
            'name'       => $name,
            'input_name' => $input_name,
            'value'      => $value,
            'type'       => $type,
        ];
    }

    /**
     * @return array
     */
    public function getQueryParts()
    {
        $joins  = array_values($this->joins);
        $params = array_values($this->params);

        $where_and = null;
        if ($this->wheres_and) {
            $where_and = '('.implode(') AND (', $this->wheres_and).')';
        }

        $where_or = null;
        if ($this->wheres_or) {
            $where_or = '('.implode(') OR (', $this->wheres_or).')';
        }

        foreach ($joins as &$j) {
            if ($j['condition']) {
                $j['condition'] = str_replace(array_keys($this->var_renamed), array_values($this->var_renamed), $j['condition']);
            }
        }

        if ($where_or || $where_and) {
            $where = [$where_and, $where_or];
            $where = Arrays::removeFalsey($where);
            $where = '('.implode(') AND (', $where).')';
            $where = str_replace(array_keys($this->var_renamed), array_values($this->var_renamed), $where);
        } else {
            $where = '1';
        }

        return [
            'joins'  => $joins,
            'params' => $params,
            'where'  => $where,
        ];
    }
}
