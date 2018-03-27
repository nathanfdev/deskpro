<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql;

/**
 * This represents a SELECT query that will be passed to MySQL. It is used to
 * create a query in a non-linear fashion.
 */
class SqlSelect
{
    /**
     * The last compiled SQL string.
     *
     * @var string
     */
    private static $lastSql;

    /**
     * List of fields/expressions in the SELECT clause. Joined by commas.
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * Name of table for the FROM clause. This must be a table name
     * rather than a full expression.
     *
     * @var string
     */
    protected $_table;

    /**
     * List of joins to add. Each join must be keyed by a unique identifier
     * to prevent adding duplicates.
     *
     * @var array
     */
    protected $_joins = [];

    /**
     * List of conditions for the WHERE clause. These will be joined by ANDs.
     *
     * @var array
     */
    protected $_conditions = [];

    /**
     * List of expressions/fields for the GROUP BY clause. Joined by commas.
     *
     * @var array
     */
    protected $_groupBy = [];

    /**
     * List of expressions/fields for the ORDER BY clause. Joined by commas.
     *
     * @var array
     */
    protected $_orderBy = [];

    /**
     * The amount of rows to fetch. If null or 0, rows will not be limited.
     *
     * @var int|null
     */
    protected $_limitAmount = null;

    /**
     * The number of rows to skip before returning results. If null or 0,
     * rows will not be limited.
     *
     * @var int|null
     */
    protected $_limitOffset = null;

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->_table = $table;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * Adds a field to the select list. This returns a 1-based index
     * identifying the position of the column being selected. This is
     * 1-based to correspond with how MySQL returns results when returning
     * number-based keys.
     *
     * @param string $string
     *
     * @return int
     */
    public function addSelectField($string)
    {
        $this->_fields[]       = $string;
        $this->_lastFieldAdded = true;

        end($this->_fields);

        return key($this->_fields) + 1;
    }

    /**
     * @return array
     */
    public function getSelectFields()
    {
        return $this->_fields;
    }

    /**
     * Gets the specified select field.
     *
     * @param int $id The 1-based ID of the field to look up
     *
     * @return string|false False if the field cannot be found
     */
    public function getSelectField($id)
    {
        return isset($this->_fields[$id - 1]) ? $this->_fields[$id - 1] : false;
    }

    /**
     * @param string $name   Unique identifier for the join
     * @param string $string
     *
     * @return bool True if the join was added, false if the join was there already
     */
    public function addJoin($name, $string)
    {
        if (isset($this->_joins[$name])) {
            return false;
        }

        $this->_joins[$name] = $string;

        return true;
    }

    /**
     * Sets all joins.
     *
     * @param array $joins
     */
    public function setJoins(array $joins)
    {
        $this->_joins = $joins;
    }

    /**
     * @return array
     */
    public function getJoins()
    {
        return $this->_joins;
    }

    /**
     * Sets all conditions.
     *
     * @param array $conditions
     */
    public function setConditions(array $conditions)
    {
        $this->_conditions = $conditions;
    }

    /**
     * @param string $condition
     */
    public function addCondition($condition)
    {
        $this->_conditions[] = $condition;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->_conditions;
    }

    /**
     * @param string $string
     */
    public function addGroupBy($string)
    {
        $this->_groupBy[] = $string;
    }

    /**
     * @return array
     */
    public function getGroupBy()
    {
        return $this->_groupBy;
    }

    /**
     * @param string $string
     */
    public function addOrderBy($string)
    {
        $this->_orderBy[] = $string;
    }

    /**
     * @return array
     */
    public function getOrderBy()
    {
        return $this->_orderBy;
    }

    /**
     * Sets the limit clause.
     *
     * @param int      $amount
     * @param int|null $offset
     */
    public function setLimit($amount, $offset = null)
    {
        $this->_limitAmount = $amount;
        if ($offset !== null) {
            $this->_limitOffset = $offset;
        }
    }

    /**
     * @return int|null
     */
    public function getLimitAmount()
    {
        return $this->_limitAmount;
    }

    /**
     * @return int|null
     */
    public function getLimitOffset()
    {
        return $this->_limitOffset;
    }

    /**
     * Gets the results as runnable SQL (SELECT statement).
     *
     * @return string
     */
    public function toSql()
    {
        if ($this->_limitAmount) {
            $limit = $this->_limitAmount.($this->_limitOffset ? ' OFFSET '.$this->_limitOffset : '');
        } elseif ($this->_limitOffset) {
            $limit = '999999 OFFSET '.$this->_limitOffset;
        } else {
            $limit = false;
        }

        $sql = 'SELECT '.implode(', ', $this->_fields)
            ."\nFROM `$this->_table`"
            .($this->_joins ? "\n".implode("\n", $this->_joins) : '')
            .($this->_conditions ? "\nWHERE ".implode(' AND ', $this->_conditions) : '')
            .($this->_groupBy ? "\nGROUP BY ".implode(', ', $this->_groupBy) : '')
            .($this->_orderBy ? "\nORDER BY ".implode(', ', $this->_orderBy) : '')
            .($limit ? "\nLIMIT $limit" : '');

        self::$lastSql = $sql;

        return $sql;
    }

    /**
     * Get the last query that was compiled into SQL.
     *
     * @return string
     */
    public static function getLastCompiledSql()
    {
        return self::$lastSql;
    }

    /**
     * Converts the object to a string (SQL SELECT statement).
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toSql();
    }

    /**
     * Quotes the value as a literal string in SQL.
     *
     * @param string $value
     *
     * @return string
     */
    public function quoteForSql($value)
    {
        return \Application\DeskPRO\App::getDb()->quote($value);
    }
}
