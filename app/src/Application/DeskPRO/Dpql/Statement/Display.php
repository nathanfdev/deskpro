<?php

namespace Application\DeskPRO\Dpql\Statement;

use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\App;

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

	protected $_sql;
	protected $_resultHandler;

	protected $_fieldMap = array();

	protected $_prepared = false;

	protected $_tableEntityMap = array(
		'tickets' => 'DeskPRO:Ticket',
		'tickets_messages' => 'DeskPRO:TicketMessage'
	);

	public function __construct($display, array $select, $from)
	{
		$this->setDisplay($display);
		$this->setSelect($select);
		$this->setFrom($from);

		$this->_sql = new Dpql\SqlSelect();
		$this->_resultHandler = new Dpql\ResultHandler();
	}

	public function toSql()
	{
		if (!$this->_prepared) {
			$this->prepare();
		}

		return $this->_sql->toSql();
	}

	public function getResults()
	{
		return App::getDb()->executeQuery($this->toSql())->fetchAll(\PDO::FETCH_NUM);
	}

	public function getResultHandler()
	{
		if (!$this->_prepared) {
			$this->prepare();
		}

		return $this->_resultHandler;
	}

	public function getRenderer($renderer, array $results = null)
	{
		if ($results === null) {
			$results = $this->getResults();
		}

		$handler = $this->getResultHandler();

		return new Dpql\Renderer\Html($handler, $results);
	}

	public function prepare()
	{
		if ($this->_prepared) return;
		$this->_prepared = true;

		$repository = $this->getFromEntityRepository();
		if ($repository) {
			$this->_sql->setTable($repository->getTableName());
		} else {
			$this->_sql->setTable('NULL');
		}

		$this->_prepareSelect();
		$this->_prepareWhere();
		$this->_prepareSplitBy();
		$this->_prepareGroupBy();
		$this->_prepareOrderBy();

		$this->_sql->setLimit($this->_limitAmount, $this->_limitOffset);
	}

	protected function _prepareSelect()
	{
		$sql = $this->_sql;

		foreach ($this->_select AS $field) {
			if ($field instanceof Part\Alias) {
				$alias = $field->alias;
				$field = $field->value;
			} else {
				$alias = false;
			}

			$select = $field->prepare($this, 'select', array(), $sql, $this->_resultHandler);

			if ($select->hasValue()) {
				$id = $this->addSqlSelectField($select->printed(), $alias);

				$resultTitle = ($alias !== false ? $alias : $select->name());
				$this->_resultHandler->addSelectColumn($resultTitle, $id, $select->renderer());
			}
		}
	}

	protected function _prepareWhere()
	{
		if ($this->_where) {
			$where = $this->_where->prepare($this, 'where', array(), $this->_sql, $this->_resultHandler);
			if ($where->hasValue()) {
				$this->_sql->addCondition($where->sql());
			}
		}
	}

	protected function _prepareSplitBy()
	{
		$sql = $this->_sql;

		foreach ($this->_splitBy AS $group) {
			$groupBy = $group->prepare($this, 'split', array(), $sql, $this->_resultHandler);
			if ($groupBy->hasValue()) {
				$id = $sql->addSelectField($groupBy->printed());
				$sql->addGroupBy($groupBy->sql());

				$this->_resultHandler->addSplitColumn($id, $groupBy->renderer());
			}
		}
	}

	protected function _prepareGroupBy()
	{
		$sql = $this->_sql;

		foreach ($this->_groupBy AS $group) {
			$groupBy = $group->prepare($this, 'group', array(), $sql, $this->_resultHandler);
			if ($groupBy->hasValue()) {
				$id = $sql->addSelectField($groupBy->printed());
				$sql->addGroupBy($groupBy->sql());

				$this->_resultHandler->addGroupYColumn($groupBy->name(), $id, $groupBy->renderer());
			}
		}
	}

	protected function _prepareOrderBy()
	{
		$sql = $this->_sql;

		foreach ($this->_orderBy AS $order) {
			if ($order instanceof Part\OrderDir) {
				$direction = ' ' . $order->orderDir;
				$order = $order->order;
			} else {
				$direction = false;
			}

			$orderSql = $order->prepare($this, 'order', array(), $sql, $this->_resultHandler);
			if ($orderSql->hasValue()) {
				$sql->addOrderBy($orderSql->sql() . $direction);
			}
		}
	}

	public function addSqlSelectField($select, $alias = false)
	{
		$selectFieldId = $this->_sql->addSelectField($select);

		if ($alias !== false) {
			$this->_fieldMap[$alias] = $selectFieldId;
		}

		return $selectFieldId;
	}

	public function getSqlSelectFieldId($key)
	{
		if (isset($this->_fieldMap[$key])) {
			return $this->_fieldMap[$key];
		} else {
			return false;
		}
	}

	public function getFromEntityRepository()
	{
		$table = strtolower($this->_from);
		if (!isset($this->_tableEntityMap[$table])) {
			return false;
		}

		return App::getEntityRepository($this->_tableEntityMap[$table]);
	}

	public function isSqlValue($input)
	{
		return strval($input) !== '';
	}

	public function stackForcedUtc(array $stack)
	{
		foreach ($stack AS $element) {
			if ($element instanceof \Application\DeskPRO\Dpql\Statement\Part\FunctionCall
				&& strtoupper($element->name) == 'UTC'
			) {
				return true;
			}
		}

		return false;
	}

	public function getTimezoneOffsetForFunction(array $stack)
	{
		if ($this->stackForcedUtc($stack)) {
			return 0;
		}

		return App::getCurrentPerson()->getTimezoneOffset() * 3600;
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