<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

class DbalQuery
{
    const JOIN_LEFT  = 'LEFT';
    const JOIN_RIGHT = 'RIGHT';
    const JOIN_INNER = 'INNER';

    const ORDER_DESC = 'DESC';
    const ORDER_ASC  = 'ASC';

    /**
     * @var array
     */
    private $params;

    /**
     * @var array
     */
    private $select_pieces;

    /**
     * @var string
     */
    private $from_table;

    /**
     * @var string
     */
    private $from_alias;

    /**
     * @var string
     */
    private $where;

    /**
     * @var array
     */
    private $joins;

    /**
     * @var array
     */
    private $join_ons;

    /**
     * @var array
     */
    private $unique_joins;

    /**
     * @var array
     */
    private $unique_join_ons;

    /**
     * @var array
     */
    private $unique_join_types;

    /**
     * @var array
     */
    private $groupings;

    /**
     * @var array
     */
    private $orderings;

    /**
     * @var int|null
     */
    private $limit;

    /**
     * @var int
     */
    private $page;

    /**
     * @var bool
     */
    private $group_with_rollup;

    public function __construct()
    {
        $this->page              = 1;
        $this->select_pieces     = array('*');
        $this->joins             = array();
        $this->join_ons          = array();
        $this->unique_joins      = array();
        $this->unique_join_ons   = array();
        $this->unique_join_types = array();
        $this->groupings         = array();
        $this->orderings         = array();
        $this->params            = array();
        $this->group_with_rollup = true; // uses GROUP BY .. WITH ROLLUP by default
    }

    public function __toString()
    {
        $sql_string = '';

        $sql_string = sprintf('SELECT %s FROM %s', $this->generateSelectString(), $this->generateFromString());

        if (count($this->joins)) {
            $sql_string .= ' '.$this->generateJoinString();
        }

        if (count($this->unique_joins)) {
            $sql_string .= ' '.$this->generateUniqueJoinString();
        }

        if (strlen($this->where) > 0) {
            $sql_string .= sprintf(' WHERE (%s)', $this->generateWhereString());
        }

        if (count($this->groupings)) {
            $sql_string .= ' GROUP BY '.$this->generateGroupByString();
            if ($this->group_with_rollup) {
                $sql_string .= ' WITH ROLLUP';
            }
        }

        if (count($this->orderings)) {
            $sql_string .= ' ORDER BY '.$this->generateOrderByString();
        }

        if ($this->limit) {
            $sql_string .= ' LIMIT '.$this->generateLimitString();
        }

        $sql_string = str_replace(
            '{from}',
            ($this->from_alias ? $this->from_alias : $this->from_table),
            $sql_string
        );

        return $sql_string;
    }

    public function getFromTable()
    {
        return $this->from_table;
    }

    public function generateSelectString()
    {
        return $this->getSelectPart();
    }

    public function getSelectPart()
    {
        return implode(', ', $this->select_pieces);
    }

    public function generateWhereString()
    {
        return $this->where;
    }

    public function setFromTable($table)
    {
        $this->from_table = trim($table);
    }

    public function generateFromString()
    {
        $table = $this->from_table;

        return $this->from_alias ? $table.' '.$this->from_alias : $table;
    }

    public function addSelectPart($select)
    {
        $this->select_pieces[] = trim($select);
    }

    public function setSelectPart($select)
    {
        $this->select_pieces = array();
        $this->addSelectPart($select);
    }

    public function setFrom($table, $alias = null)
    {
        $this->from_table = $table;
        $this->from_alias = $alias;
    }

    public function setFromAlias($alias)
    {
        $this->from_alias = trim($alias);
    }

    public function getFromAlias()
    {
        return $this->from_alias;
    }

    public function setWherePart($where)
    {
        $this->where = trim($where);
    }

    public function appendWhere($append_to_where)
    {
        $this->where .= ' '.trim($append_to_where);
    }

    public function hasJoin($alias)
    {
        return array_key_exists($alias, $this->joins);
    }

    public function addJoin($table, $on, $alias = null)
    {
        if (!$alias) {
            $alias = $table;
        }
        $this->joins[$alias] = array($table, $on);
    }

    public function generateJoinString()
    {
        $join_string = '';

        foreach ($this->joins as $alias => $join) {
            $table = $join[0];
            $on    = $join[1];

            if ($table === $alias) {
                $join_string .= sprintf('%s JOIN %s ON (%s) ', self::JOIN_LEFT, $table, $on);
            } else {
                $join_string .= sprintf('%s JOIN %s %s ON (%s) ', self::JOIN_LEFT, $table, $alias, $on);
            }
        }

        // trim because it will always have a trailing space from loop
        return trim($join_string);
    }

    public function generateUniqueJoinString()
    {
        $join_string = '';

        foreach ($this->unique_joins as $alias => $table) {
            $type = $this->unique_join_types[$alias];
            $on   = $this->unique_join_ons[$alias];

            $join_string .= sprintf('%s JOIN %s %s ON (%s) ', $type, $table, $alias, $on);
        }

        // trim because it will always have a trailing space from loop
        return trim($join_string);
    }

    public function addUniqueJoin($table, $on, $type)
    {
        $alias = $table.'_0';

        for ($i = 1; array_key_exists($alias, $this->unique_joins); ++$i) {
            $alias = $table.'_'.$i;
        }

        $on = str_replace('{alias}', $alias, $on);

        $this->unique_joins[$alias]      = $table;
        $this->unique_join_ons[$alias]   = $on;
        $this->unique_join_types[$alias] = $type;

        return $alias;
    }

    public function addParameter($name_prefix, $value)
    {
        $p_name = $name_prefix.'_0';

        for ($i = 1; array_key_exists($p_name, $this->params); ++$i) {
            $p_name = $name_prefix.'_'.$i;
        }

        $this->params[$p_name] = $value;

        return $p_name;
    }

    public function getParameters()
    {
        return $this->params;
    }

    public function getParameter($param)
    {
        if (array_key_exists($param, $this->params)) {
            return $this->params[$param];
        }

        return;
    }

    public function replaceParameter($param, $value)
    {
        if (!array_key_exists($param, $this->params)) {
            throw new \InvalidArgumentException(
                sprintf('param "%s" does not exist. use addParameter() instead.', $param)
            );
        }

        $this->params[$param] = $value;
    }

    public function addGroupBy($group_by)
    {
        $this->groupings[] = $group_by;
    }

    public function getGroupBy()
    {
        return $this->groupings;
    }

    public function generateGroupByString()
    {
        return implode(', ', $this->groupings);
    }

    public function addOrderBy($order_by, $direction)
    {
        $this->orderings[] = array($order_by, $direction);
    }

    public function getOrderBy()
    {
        return $this->orderings;
    }

    public function generateOrderByString()
    {
        $parts = array();

        foreach ($this->orderings as $order) {
            $parts[] = $order[0].' '.$order[1];
        }

        return implode(', ', $parts);
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function generateLimitString()
    {
        if (!$this->limit) {
            return '';
        }

        if (($offset = $this->getPageOffset()) > 0) {
            return sprintf('%s, %s', $offset, $this->limit);
        }

        return sprintf('%s', $this->limit);
    }

    public function getPageOffset()
    {
        if (!$this->limit || !$this->page) {
            return;
        }

        return ($this->page - 1) * $this->limit;
    }

    /**
     * @return bool
     */
    public function isGroupWithRollup()
    {
        return $this->group_with_rollup;
    }

    /**
     * @param bool $group_with_rollup
     */
    public function setGroupWithRollup($group_with_rollup)
    {
        $this->group_with_rollup = (bool) $group_with_rollup;
    }
}
