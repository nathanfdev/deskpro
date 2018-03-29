<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

/**
 * Class DbalQuery.
 */
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
     * @var int
     */
    private $offset;

    /**
     * @var bool
     */
    private $group_with_rollup;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->page              = 1;
        $this->select_pieces     = ['*'];
        $this->joins             = [];
        $this->join_ons          = [];
        $this->unique_joins      = [];
        $this->unique_join_ons   = [];
        $this->unique_join_types = [];
        $this->groupings         = [];
        $this->orderings         = [];
        $this->params            = [];
        $this->group_with_rollup = true; // uses GROUP BY .. WITH ROLLUP by default
    }

    /**
     * @return string
     */
    public function __toString()
    {
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

    /**
     * @return string
     */
    public function getFromTable()
    {
        return $this->from_table;
    }

    /**
     * @return string
     */
    public function generateSelectString()
    {
        return $this->getSelectPart();
    }

    /**
     * @return string
     */
    public function getSelectPart()
    {
        return implode(', ', $this->select_pieces);
    }

    /**
     * @return string
     */
    public function generateWhereString()
    {
        return $this->where;
    }

    /**
     * @param $table
     */
    public function setFromTable($table)
    {
        $this->from_table = trim($table);
    }

    /**
     * @return string
     */
    public function generateFromString()
    {
        $table = $this->from_table;

        return $this->from_alias ? $table.' '.$this->from_alias : $table;
    }

    /**
     * @param $select
     */
    public function addSelectPart($select)
    {
        $this->select_pieces[] = trim($select);
    }

    /**
     * @param $select
     */
    public function setSelectPart($select)
    {
        $this->select_pieces = [];
        $this->addSelectPart($select);
    }

    /**
     * @param string $table
     * @param string $alias
     */
    public function setFrom($table, $alias = null)
    {
        $this->from_table = $table;
        $this->from_alias = $alias;
    }

    /**
     * @param $alias
     */
    public function setFromAlias($alias)
    {
        $this->from_alias = trim($alias);
    }

    /**
     * @return string
     */
    public function getFromAlias()
    {
        return $this->from_alias;
    }

    /**
     * @param $where
     */
    public function setWherePart($where)
    {
        $this->where = trim($where);
    }

    /**
     * @param $append_to_where
     */
    public function appendWhere($append_to_where)
    {
        $this->where .= ' '.trim($append_to_where);
    }

    /**
     * @param string $alias
     *
     * @return bool
     */
    public function hasJoin($alias)
    {
        return array_key_exists($alias, $this->joins);
    }

    /**
     * @param string $table
     * @param string $on
     * @param string $alias
     */
    public function addJoin($table, $on, $alias = null)
    {
        if (!$alias) {
            $alias = $table;
        }
        $this->joins[$alias] = [$table, $on];
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
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

    /**
     * @param string $table
     * @param string $on
     * @param string $type
     *
     * @return string
     */
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

    /**
     * @param string $name_prefix
     * @param mixed  $value
     *
     * @return string
     */
    public function addParameter($name_prefix, $value)
    {
        $p_name = $name_prefix.'_0';

        for ($i = 1; array_key_exists($p_name, $this->params); ++$i) {
            $p_name = $name_prefix.'_'.$i;
        }

        $this->params[$p_name] = $value;

        return $p_name;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->params;
    }

    /**
     * @param $param
     */
    public function getParameter($param)
    {
        if (array_key_exists($param, $this->params)) {
            return $this->params[$param];
        }

        return;
    }

    /**
     * @param string $param
     * @param mixed  $value
     */
    public function replaceParameter($param, $value)
    {
        if (!array_key_exists($param, $this->params)) {
            throw new \InvalidArgumentException(
                sprintf('param "%s" does not exist. use addParameter() instead.', $param)
            );
        }

        $this->params[$param] = $value;
    }

    /**
     * @param $group_by
     */
    public function addGroupBy($group_by)
    {
        $this->groupings[] = $group_by;
    }

    /**
     * @return array
     */
    public function getGroupBy()
    {
        return $this->groupings;
    }

    /**
     * @return string
     */
    public function generateGroupByString()
    {
        return implode(', ', $this->groupings);
    }

    /**
     * @param string $order_by
     * @param string $direction
     */
    public function addOrderBy($order_by, $direction)
    {
        $this->orderings[] = [$order_by, $direction];
    }

    /**
     * @return array
     */
    public function getOrderBy()
    {
        return $this->orderings;
    }

    /**
     * @return string
     */
    public function generateOrderByString()
    {
        $parts = [];
        foreach ($this->orderings as $order) {
            $parts[] = $order[0].' '.$order[1];
        }

        return implode(', ', $parts);
    }

    /**
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @param $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return string
     */
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

    /**
     * @return int
     */
    public function getPageOffset()
    {
        if ($this->offset) {
            return $this->offset;
        }

        if (!$this->limit || !$this->page) {
            return 0;
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
