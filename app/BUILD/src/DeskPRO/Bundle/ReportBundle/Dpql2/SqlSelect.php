<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2;

use Doctrine\DBAL\Connection;

/**
 * This represents a SELECT query that will be passed to MySQL. It is used to
 * create a query in a non-linear fashion.
 */
class SqlSelect
{
    /**
     * @var Connection
     */
    private $connection;

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
    protected $fields = [];

    /**
     * Name of table for the FROM clause. This must be a table name
     * rather than a full expression.
     *
     * @var string
     */
    protected $table;

    /**
     * List of joins to add. Each join must be keyed by a unique identifier
     * to prevent adding duplicates.
     *
     * @var array
     */
    protected $joins = [];

    /**
     * List of conditions for the WHERE clause. These will be joined by ANDs.
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * List of expressions/fields for the GROUP BY clause. Joined by commas.
     *
     * @var array
     */
    protected $groupBy = [];

    /**
     * List of expressions/fields for the ORDER BY clause. Joined by commas.
     *
     * @var array
     */
    protected $orderBy = [];

    /**
     * The amount of rows to fetch. If null or 0, rows will not be limited.
     *
     * @var int|null
     */
    protected $limitAmount = null;

    /**
     * The number of rows to skip before returning results. If null or 0,
     * rows will not be limited.
     *
     * @var int|null
     */
    protected $limitOffset = null;

    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
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
        // fields auto-link can cause dupes, just return field if it already exists
        if (in_array($string, $this->fields)) {
            return array_search($string, $this->fields) + 1;
        }

        $this->fields[]        = $string;
        $this->_lastFieldAdded = true;

        end($this->fields);

        return key($this->fields) + 1;
    }

    /**
     * @return array
     */
    public function getSelectFields()
    {
        return $this->fields;
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
        return isset($this->fields[$id - 1]) ? $this->fields[$id - 1] : false;
    }

    /**
     * @param string $name   Unique identifier for the join
     * @param string $string
     *
     * @return bool True if the join was added, false if the join was there already
     */
    public function addJoin($name, $string)
    {
        if (isset($this->joins[$name])) {
            return false;
        }

        $this->joins[$name] = $string;

        return true;
    }

    /**
     * Sets all joins.
     *
     * @param array $joins
     */
    public function setJoins(array $joins)
    {
        $this->joins = $joins;
    }

    /**
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * Sets all conditions.
     *
     * @param array $conditions
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @param string $condition
     */
    public function addCondition($condition)
    {
        $this->conditions[] = $condition;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param string $string
     */
    public function addGroupBy($string)
    {
        $this->groupBy[] = $string;
    }

    /**
     * @return array
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @param string $string
     */
    public function addOrderBy($string)
    {
        $this->orderBy[] = $string;
    }

    /**
     * @return array
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Sets the limit clause.
     *
     * @param int      $amount
     * @param int|null $offset
     */
    public function setLimit($amount, $offset = null)
    {
        $this->limitAmount = $amount;
        if ($offset !== null) {
            $this->limitOffset = $offset;
        }
    }

    /**
     * @return int|null
     */
    public function getLimitAmount()
    {
        return $this->limitAmount;
    }

    /**
     * @return int|null
     */
    public function getLimitOffset()
    {
        return $this->limitOffset;
    }

    /**
     * Gets the results as runnable SQL (SELECT statement).
     *
     * @return string
     */
    public function toSql()
    {
        if ($this->limitAmount) {
            $limit = $this->limitAmount.($this->limitOffset ? ' OFFSET '.$this->limitOffset : '');
        } elseif ($this->limitOffset) {
            $limit = '999999 OFFSET '.$this->limitOffset;
        } else {
            $limit = false;
        }

        $sql = 'SELECT '.implode(', ', $this->fields);

        if (is_string($this->table)) {
            $sql .= "\nFROM `$this->table`";
        } else {
            $sql .= "\nFROM {$this->table[0]} AS {$this->table[1]}";
        }

        $sql .= ($this->joins ? "\n".implode("\n", $this->joins) : '');
        $sql .= ($this->conditions ? "\nWHERE ".implode(' AND ', $this->conditions) : '');
        $sql .= ($this->groupBy ? "\nGROUP BY ".implode(', ', $this->groupBy) : '');
        $sql .= ($this->orderBy ? "\nORDER BY ".implode(', ', $this->orderBy) : '');
        $sql .= ($limit ? "\nLIMIT $limit" : '');

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
        return $this->connection->quote($value);
    }
}
