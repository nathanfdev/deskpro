<?php

namespace Application\DeskPRO\Dpql\Statement;

class Display
{
	protected $_display = 'table';
	protected $_select = array();
	protected $_from;
	protected $_where;
	protected $_splitBy;
	protected $_groupBy;
	protected $_orderBy;

	protected $_limitAmount = null;
	protected $_limitOffset = null;

	public function __construct($display = null, array $select = null, $from = null)
	{
		if ($display !== null) $this->setDisplay($display);
		if ($select !== null) $this->setSelect($select);
		if ($from !== null) $this->setFrom($from);
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

	public function addSelect($select)
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

	public function setWhere($where)
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