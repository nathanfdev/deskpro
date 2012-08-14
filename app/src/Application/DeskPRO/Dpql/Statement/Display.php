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
		'articles' => 'DeskPRO:Article',
		//'article_attachments' => 'DeskPRO:ArticleAttachment',
		//'article_comments' => 'DeskPRO:ArticleComment',
		//'blobs' => 'DeskPRO:Blob',
		'chat_conversations' => 'DeskPRO:ChatConversation',
		//'chat_messages' => 'DeskPRO:ChatMessage', <-- there is no entity repository for this
		'downloads' => 'DeskPRO:Downloads',
		//'download_comments' => 'DeskPRO:DownloadComment',
		'feedback' => 'DeskPRO:Feedback',
		//'feedback_attachments' => 'DeskPRO:FeedbackAttachment',
		//'feedback_comments' => 'DeskPRO:FeedbackComment',
		/*'labels_articles' => 'DeskPRO:LabelArticle',
		'labels_blobs' => 'DeskPRO:LabelBlob',
		'labels_downloads' => 'DeskPRO:LabelDownload',
		'labels_feedback' => 'DeskPRO:LabelFeedback',
		'labels_news' => 'DeskPRO:LabelNews',
		'labels_organizations' => 'DeskPRO:LabelOrganization',
		'labels_people' => 'DeskPRO:LabelPerson',
		'labels_tasks' => 'DeskPRO:LabelTask',
		'labels_tickets' => 'DeskPRO:LabelTicket',*/
		'news' => 'DeskPRO:News',
		//'news_comments' => 'DeskPRO:NewsComment',
		'organizations' => 'DeskPRO:Organizations',
		'people' => 'DeskPRO:Person',
		//'tasks' => 'DeskPRO:Task',
		//'task_comments' => 'DeskPRO:TaskComment',
		'tickets' => 'DeskPRO:Ticket',
		//'ticket_attachments' => 'DeskPRO:TicketAttachment',
		//'ticket_feedback' => 'DeskPRO:TicketFeedback',
		'tickets_messages' => 'DeskPRO:TicketMessage',
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

	public function getDpqlParts()
	{
		$selectFields = array();
		foreach ($this->_select AS $field) {
			$selectFields[] = $field->toDpql($this, 'select', array());
		}

		$splitFields = array();
		foreach ($this->_splitBy AS $field) {
			$splitFields[] = $field->toDpql($this, 'split', array());
		}

		$groupFields = array();
		foreach ($this->_groupBy AS $field) {
			$groupFields[] = $field->toDpql($this, 'group', array());
		}

		$orderFields = array();
		foreach ($this->_orderBy AS $field) {
			$orderFields[] = $field->toDpql($this, 'order', array());
		}

		return array(
			'DISPLAY' => strtoupper($this->_display),
			'SELECT' => implode(', ', $selectFields),
			'FROM' => $this->_from,
			'WHERE' => ($this->_where ? $this->_where->toDpql($this, 'where', array()) : ''),
			'SPLIT' => implode(', ', $splitFields),
			'GROUP' => implode(', ', $groupFields),
			'ORDER' => implode(', ', $orderFields),
			'LIMIT' => $this->_limitAmount,
			'OFFSET' => $this->_limitOffset
		);
	}

	public function prepare()
	{
		if ($this->_prepared) return;
		$this->_prepared = true;

		$repository = $this->getFromEntityRepository();
		if ($repository) {
			$this->_sql->setTable($repository->getTableName());
		} else {
			throw new \Exception("Unknown table $this->_from in FROM clause.");
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

		$repositoryName = $this->_tableEntityMap[$table];
		$repository = App::getEntityRepository($repositoryName);

		if (!method_exists($repository, 'getTableName')) {
			throw new \Exception("$repositoryName does not extend AbstractEntityRepository so cannot be queried.");
		} else {
			return $repository;
		}
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

	public function quoteDpqlString($string)
	{
		$string = strtr($string, array("\\" => "\\\\", "'" => "\\'"));
		return "'$string'";
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