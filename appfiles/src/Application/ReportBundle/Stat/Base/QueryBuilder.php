<?php

namespace Application\ReportBundle\Stat\Base;

/**
 * Basic Query builder class.
 *
 * Could probably use the Doctrine\DBAL\Query\QueryBuilder class with this
 * aswell
 */
class QueryBuilder
{
	protected $selects = array();

	protected $from = '';

	protected $joins = array();

	protected $wheres = array();

	protected $group_by = array();

	protected $order_by = array();

	protected $limit_by = null;

	protected $offset = null;

	public function addSelect($field)
	{
		$this->selects[] = $field;
	}

	public function addFrom($table)
	{
		$this->from = $table;
	}

	public function addJoin($join)
	{
		$this->joins[] = $join;
	}

	public function addWhere($where)
	{
		$this->wheres[] = $where;
	}

	public function addGroupBy($field)
	{
		$this->group_by[] = $field;
	}

	public function addOrderBy($field)
	{
		$this->order_by[] = $field;
	}

	public function addLimitBy($limit, $offset = null)
	{
		$this->limit_by = $limit;
		$this->offset   = $offset;
	}

	public function getSelects()
	{
		return $this->selects;
	}

	public function getFrom()
	{
		return $this->from;
	}

	public function getJoins()
	{
		return $this->joins;
	}

	public function getGroupBy()
	{
		return $this->group_by;
	}

	public function getOrderBy()
	{
		return $this->order_by;
	}

	public function getLimit()
	{
		return $this->limit_by;
	}

	public function getOffset()
	{
		return $this->offset;
	}

	public function isFieldSelected($field)
	{
		return isset($this->selects[$field]) ? true : false;
	}

	/**
	 * Build the SQL query
	 */
	public function getSql()
	{
		// Get the select part
		$select = $this->getSqlSelect();

		// Get the form part
		$from   = $this->getSqlFrom();

		// Get the where part
		$where  = $this->getSqlWhere();

		// Get the join part
		$join   = $this->getSqlJoin();

		// Get the group by part
		$groupBy = $this->getSqlGroupBy();

		// Get the order by part
		$orderBy  = $this->getSqlOrderBy();

		// Get the limit part
		$limit    = $this->getSqlLimit();

		$queryParts = array(
			$select,
			$from,
			$where,
			$join,
			$groupBy,
			$orderBy,
			$limit
		);

		return trim(join(' ', $queryParts));
	}

	/**
	 * Build the select part of the query
	 *
	 * @return string The select statement
	 */
	protected function getSqlSelect()
	{
		if (!count($this->selects)) {
			throw new \Exception("You must select at least one field");
		}

		return "SELECT " . join(", ", $this->selects);
	}

	protected function getSqlFrom()
	{
		if (0 === strlen($this->from)) {
			throw new \Exception("You specify a table to select from");
		}

		return "FROM " . $this->from;
	}

	protected function getSqlWhere()
	{
		$whereSql = "";
		if (count($this->wheres)) {
			$whereSql = "WHERE " . join(" AND ", $this->wheres);
		}

		return $whereSql;
	}

	protected function getSqlJoin()
	{
		return join(" ", $this->joins);
	}

	protected function getSqlGroupBy()
	{
		$groupBySql = "";
		if (count($this->group_by)) {
			$groupBySql = "GROUP BY " . join(", ", $this->group_by);
		}

		return $groupBySql;
	}

	protected function getSqlOrderBy()
	{
		$orderBySql = "";
		if (count($this->order_by)) {
			$groupBySql = "ORDER BY " . join(", ", $this->order_by);
		}

		return $orderBySql;
	}

	protected function getSqlLimit()
	{
		$limitSql = "";
		if (false === is_null($this->limit_by)) {
			$limitSql = "LIMIT " . $this->limit_by;
		}

		return $limitSql;
	}
}