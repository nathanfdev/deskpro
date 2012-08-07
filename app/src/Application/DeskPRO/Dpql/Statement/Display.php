<?php

namespace Application\DeskPRO\Dpql\Statement;

use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;

class Display
{
	protected $_display = 'table';
	protected $_select = array();
	protected $_from;
	protected $_where;
	protected $_splitBy = array();
	protected $_groupBy = array();
	protected $_orderBy = array();

	protected $_limitAmount = null;
	protected $_limitOffset = null;

	protected $_extraSelectSql = array();
	protected $_joins = array();
	protected $_sql;

	public function __construct($display = null, array $select = null, $from = null)
	{
		if ($display !== null) $this->setDisplay($display);
		if ($select !== null) $this->setSelect($select);
		if ($from !== null) $this->setFrom($from);
	}

	public function toSql()
	{
		if (!$this->_sql) {
			$this->prepare();
		}

		return $this->_sql;
	}

	public function prepare()
	{
		$this->_extraSelectSql = array();
		$this->_joins = array();

		$select = array();
		foreach ($this->_select AS $field) {
			$select[] = $field->toSql($this, 'select', array());
		}

		if ($this->_where) {
			$where = $this->_where->toSql($this, 'where', array());
		} else {
			$where = false;
		}

		$groups = array();
		foreach ($this->_splitBy AS $split) {
			$groups[] = $split->toSql($this, 'split', array());
		}
		foreach ($this->_groupBy AS $group) {
			$groups[] = $group->toSql($this, 'group', array());
		}

		$orders = array();
		foreach ($this->_orderBy AS $order) {
			if (is_array($order)) {
				list($orderExpr, $orderDir) = $order;
			} else {
				$orderExpr = $order;
				$orderDir = 'ASC';
			}

			$orders[] = $orderExpr->toSql($this, 'order', array()) . " $orderDir";
		}

		if ($this->_limitAmount) {
			$limit = $this->_limitAmount . ($this->_limitOffset ? " OFFSET " . $this->_limitOffset : '');
		} else if ($this->_limitOffset) {
			$limit = '999999 OFFSET ' . $this->_limitOffset;
		} else {
			$limit = false;
		}

		if ($this->_extraSelectSql) {
			$select = array_merge($select, $this->_extraSelectSql);
		}

		$this->_sql = 'SELECT ' . implode(', ', $select)
			. "\nFROM `$this->_from`"
			. ($this->_joins ? "\n" . implode("\n", $this->_joins) : '')
			. ($where ? "\nWHERE $where" : '')
			. ($groups ? "\nGROUP BY " . implode(', ', $groups) : '')
			. ($orders ? "\nORDER BY " . implode(', ', $orders) : '')
			. ($limit ? "\nLIMIT $limit" : '');
	}

	public function addExtraSelectSql($sql)
	{
		$this->_extraSelectSql[] = $sql;
	}

	public function addJoin($name, $sql)
	{
		$this->_joins[$name] = $sql;
	}

	public function setDisplay($display)
	{
		$this->_display = $display;
	}

	public function getDisplay()
	{
		return $this->_display;
	}

	public function setSelect(array $select)
	{
		$this->_select = $select;
	}

	public function addSelect(AbstractPart $select)
	{
		$this->_select[] = $select;
	}

	public function getSelect()
	{
		return $this->_select;
	}

	public function setFrom($from)
	{
		$this->_from = $from;
	}

	public function getFrom()
	{
		return $this->_from;
	}

	public function setWhere(AbstractPart $where = null)
	{
		$this->_where = $where;
	}

	public function getWhere()
	{
		return $this->_where;
	}

	public function setSplitBy(array $splitBy)
	{
		$this->_splitBy = $splitBy;
	}

	public function getSplitBy()
	{
		return $this->_splitBy;
	}

	public function setGroupBy(array $groupBy)
	{
		$this->_groupBy = $groupBy;
	}

	public function getGroupBy()
	{
		return $this->_groupBy;
	}

	public function setOrderBy(array $orderBy)
	{
		$this->_orderBy = $orderBy;
	}

	public function getOrderBy()
	{
		return $this->_orderBy;
	}

	public function setLimitAmount($amount)
	{
		$this->_limitAmount = $amount;
	}

	public function getLimitAmount()
	{
		return $this->_limitAmount;
	}

	public function setLimitOffset($offset)
	{
		$this->_limitOffset = $offset;
	}

	public function getLimitOffset()
	{
		return $this->_limitOffset;
	}
}