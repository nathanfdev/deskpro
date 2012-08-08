<?php

namespace Application\DeskPRO\Dpql;

class SqlSelect
{
	protected $_fields = array();
	protected $_table;
	protected $_joins = array();
	protected $_conditions = array();
	protected $_groupBy = array();
	protected $_orderBy = array();
	protected $_limitAmount = null;
	protected $_limitOffset = null;

	public function setTable($table)
	{
		$this->_table = $table;
	}

	public function getTable()
	{
		return $this->_table;
	}

	public function addSelectField($string)
	{
		$this->_fields[] = $string;
		$this->_lastFieldAdded = true;

		end($this->_fields);
		return key($this->_fields) + 1;
	}

	public function getSelectFields()
	{
		return $this->_fields;
	}

	public function getSelectField($id)
	{
		return isset($this->_fields[$id - 1]) ? $this->_fields[$id - 1] : false;
	}

	public function addJoin($name, $string)
	{
		if (isset($this->_joins[$name])) {
			return false;
		}

		$this->_joins[$name] = $string;
		return true;
	}

	public function getJoins()
	{
		return $this->_joins;
	}

	public function addCondition($condition)
	{
		$this->_conditions[] = $condition;
	}

	public function getConditions()
	{
		return $this->_conditions;
	}

	public function addGroupBy($string)
	{
		$this->_groupBy[] = $string;
	}

	public function getGroupBy()
	{
		return $this->_groupBy;
	}

	public function addOrderBy($string)
	{
		$this->_orderBy[] = $string;
	}

	public function getOrderBy()
	{
		return $this->_orderBy;
	}

	public function setLimit($amount, $offset = null)
	{
		$this->_limitAmount = $amount;
		if ($offset !== null) {
			$this->_limitOffset = $offset;
		}
	}

	public function getLimitAmount()
	{
		return $this->_limitAmount;
	}

	public function getLimitOffset()
	{
		return $this->_limitOffset;
	}

	public function toSql()
	{
		if ($this->_limitAmount) {
			$limit = $this->_limitAmount . ($this->_limitOffset ? " OFFSET " . $this->_limitOffset : '');
		} else if ($this->_limitOffset) {
			$limit = '999999 OFFSET ' . $this->_limitOffset;
		} else {
			$limit = false;
		}

		return 'SELECT ' . implode(', ', $this->_fields)
			. "\nFROM `$this->_table`"
			. ($this->_joins ? "\n" . implode("\n", $this->_joins) : '')
			. ($this->_conditions ? "\nWHERE " . implode(' AND ', $this->_conditions) : '')
			. ($this->_groupBy ? "\nGROUP BY " . implode(', ', $this->_groupBy) : '')
			. ($this->_orderBy ? "\nORDER BY " . implode(', ', $this->_orderBy) : '')
			. ($limit ? "\nLIMIT $limit" : '');
	}
}