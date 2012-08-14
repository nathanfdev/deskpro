<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Dpql
 */

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

	public function escapeForSql($value)
	{
		return \Application\DeskPRO\App::getDb()->quote($value);
	}
}