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

namespace Application\DeskPRO\Dpql\Statement;

use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Results;

/**
 * Object for a DISPLAY statement in DPQL.
 */
class Display
{
	/**
	 * Type of display (only table supported now).
	 *
	 * @var array
	 */
	protected $_display = array('table');

	/**
	 * List of expressions in SELECT clause
	 *
	 * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]
	 */
	protected $_select = array();

	/**
	 * Name of table to select from
	 *
	 * @var string
	 */
	protected $_from;

	/**
	 * WHERE clause.
	 *
	 * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart|null
	 */
	protected $_where = null;

	/**
	 * SPLIT BY clause expressions
	 *
	 * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]
	 */
	protected $_splitBy = array();

	/**
	 * GROUP BY clause expressions
	 *
	 * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]
	 */
	protected $_groupBy = array();

	/**
	 * ORDER BY clause expressions
	 *
	 * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]
	 */
	protected $_orderBy = array();

	/**
	 * Number of rows to limit to. 0 or null for unlimited.
	 *
	 * @var integer|null
	 */
	protected $_limitAmount = null;

	/**
	 * Number of rows to offset results by. 0 or null for no offset.
	 *
	 * @var integer|null
	 */
	protected $_limitOffset = null;

	/**
	 * SQL select object
	 *
	 * @var \Application\DeskPRO\Dpql\SqlSelect
	 */
	protected $_sql;

	/**
	 * SQL select option for splitting, if there is a split by
	 *
	 * @var \Application\DeskPRO\Dpql\SqlSelect|null
	 */
	protected $_splitSql;

	/**
	 * Maps SQL for splitting to the ID in the result set (1-based)
	 *
	 * @var array
	 */
	protected $_splitColumnMap = array();

	/**
	 * @var \Application\DeskPRO\Dpql\ResultHandler
	 */
	protected $_resultHandler;

	/**
	 * Maps aliases (keys) to select field IDs (in the SQL).
	 *
	 * @var array
	 */
	protected $_fieldMap = array();

	/**
	 * Has this been prepared yet?
	 *
	 * @var bool
	 */
	protected $_prepared = false;

	/**
	 * Maps available tables (keys) to Doctrine entity names (values).
	 *
	 * @var array
	 */
	protected $_tableEntityMap = array(
		'articles' => 'DeskPRO:Article',
		'article_attachments' => 'DeskPRO:ArticleAttachment',
		'article_comments' => 'DeskPRO:ArticleComment',
		'blobs' => 'DeskPRO:Blob',
		'chat_conversations' => 'DeskPRO:ChatConversation',
		//'chat_messages' => 'DeskPRO:ChatMessage', <-- there is no entity repository for this
		'downloads' => 'DeskPRO:Downloads',
		'download_comments' => 'DeskPRO:DownloadComment',
		'feedback' => 'DeskPRO:Feedback',
		'feedback_attachments' => 'DeskPRO:FeedbackAttachment',
		'feedback_comments' => 'DeskPRO:FeedbackComment',
		'labels_articles' => 'DeskPRO:LabelArticle',
		'labels_blobs' => 'DeskPRO:LabelBlob',
		'labels_downloads' => 'DeskPRO:LabelDownload',
		'labels_feedback' => 'DeskPRO:LabelFeedback',
		'labels_news' => 'DeskPRO:LabelNews',
		'labels_organizations' => 'DeskPRO:LabelOrganization',
		'labels_people' => 'DeskPRO:LabelPerson',
		'labels_tasks' => 'DeskPRO:LabelTask',
		'labels_tickets' => 'DeskPRO:LabelTicket',
		'news' => 'DeskPRO:News',
		'news_comments' => 'DeskPRO:NewsComment',
		'organizations' => 'DeskPRO:Organizations',
		'people' => 'DeskPRO:Person',
		'people_emails' => 'DeskPRO:PersonEmail',
		'tasks' => 'DeskPRO:Task',
		'task_comments' => 'DeskPRO:TaskComment',
		'tickets' => 'DeskPRO:Ticket',
		'tickets_log' => 'DeskPRO:TicketLog',
		'tickets_messages' => 'DeskPRO:TicketMessage',
		'ticket_attachments' => 'DeskPRO:TicketAttachment',
		'ticket_feedback' => 'DeskPRO:TicketFeedback',
	);

	/**
	 * @param array $display Type of display (must not be empty)
	 * @param array $select Fields to select
	 * @param string $from Table to select from
	 */
	public function __construct(array $display, array $select, $from)
	{
		$this->setDisplay($display);
		$this->setSelect($select);
		$this->setFrom($from);

		$this->_sql = new Dpql\SqlSelect();
		$this->_resultHandler = new Dpql\ResultHandler();
	}

	/**
	 * Returns statement as SQL.
	 *
	 * @return string
	 */
	public function toSql()
	{
		if (!$this->_prepared) {
			$this->prepare();
		}

		return $this->_sql->toSql();
	}

	/**
	 * Gets the results from the database that match.
	 *
	 * @return \Application\DeskPRO\Dpql\Results
	 *
	 * @throws \Application\DeskPRO\Dpql\Exception
	 */
	public function getResults()
	{
		$results = new Results();
		$db = App::getDb();

		try {
			if ($this->_splitColumnMap) {
				$this->_splitSql->setTable($this->_sql->getTable());
				$this->_splitSql->setJoins($this->_sql->getJoins());
				$this->_splitSql->setConditions($this->_sql->getConditions());

				$splitResults = $db->executeQuery($this->_splitSql->toSql())->fetchAll(\PDO::FETCH_NUM);
				foreach ($splitResults AS $splitResult)
				{
					$sql = clone $this->_sql;
					foreach ($this->_splitColumnMap AS $splitCondition => $splitColumn)
					{
						$splitValue = $splitResult[$splitColumn - 1];
						$sql->addCondition("$splitCondition = " . $db->quote($splitValue));
					}

					$queryResults = $db->executeQuery($sql->toSql())->fetchAll(\PDO::FETCH_NUM);
					$results->addSplitResults($queryResults, $splitResult);
				}
			} else {
				$queryResults = $db->executeQuery($this->_sql->toSql())->fetchAll(\PDO::FETCH_NUM);
				$results->setResults($queryResults);
			}
		} catch (Exception $e) {
			throw new Exception("This DPQL statement generated an invalid MySQL query. Please try a different query.");
		}

		return $results;
	}

	/**
	 * @return \Application\DeskPRO\Dpql\ResultHandler
	 */
	public function getResultHandler()
	{
		if (!$this->_prepared) {
			$this->prepare();
		}

		return $this->_resultHandler;
	}

	/**
	 * Gets the specified renderer object.
	 *
	 * @param string $rendererType Type of renderer needed
	 * @param array|null $results If null, gets results
	 *
	 * @return \Application\DeskPRO\Dpql\Renderer\AbstractRenderer
	 */
	public function getRenderer($rendererType, array $results = null)
	{
		if ($results === null) {
			$results = $this->getResults();
		}

		$handler = $this->getResultHandler();

		return \Application\DeskPRO\Dpql\Renderer\AbstractRenderer::create(
			$rendererType, $this->_display, $handler, $results
		);
	}

	/**
	 * Gets the statement back as a string of DPQL parts. Keys are:
	 * DISPLAY, SELECT, FROM, WHERE, SPLIT, GROUP, ORDER, LIMIT, OFFSET
	 *
	 * @return array
	 */
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

		$display = array_map('strtoupper', $this->_display);

		return array(
			'DISPLAY' => $display,
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

	/**
	 * Prepares the statement for use.
	 *
	 * @throws \Application\DeskPRO\Dpql\Exception
	 */
	public function prepare()
	{
		if ($this->_prepared) return;
		$this->_prepared = true;

		$repository = $this->getFromEntityRepository();
		if ($repository) {
			$this->_sql->setTable($repository->getTableName());
		} else {
			throw new Exception("Unknown table $this->_from in FROM clause.");
		}

		$this->_prepareSelect();
		$this->_prepareWhere();
		$this->_prepareSplitBy();
		$this->_prepareGroupBy();
		$this->_prepareOrderBy();

		$this->_sql->setLimit($this->_limitAmount, $this->_limitOffset);
	}

	/**
	 * Prepares the SELECT clause.
	 */
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

	/**
	 * Prepares the WHERE clause.
	 */
	protected function _prepareWhere()
	{
		if ($this->_where) {
			$where = $this->_where->prepare($this, 'where', array(), $this->_sql, $this->_resultHandler);
			if ($where->hasValue()) {
				$this->_sql->addCondition($where->sql());
			}
		}
	}

	/**
	 * Prepares the SPLIT BY clause.
	 */
	protected function _prepareSplitBy()
	{
		if (!$this->_splitBy)
		{
			return;
		}

		$splitSql = new Dpql\SqlSelect();
		$haveSplit = false;

		foreach ($this->_splitBy AS $group) {
			$groupBy = $group->prepare($this, 'split', array(), $this->_sql, $this->_resultHandler);
			if ($groupBy->hasValue()) {
				$splitSql->addGroupBy($groupBy->sql());

				$this->_splitColumnMap[$groupBy->sql()] = $splitSql->addSelectField($groupBy->sql());

				$id = $splitSql->addSelectField($groupBy->printed());
				$this->_resultHandler->addSplitColumn($id, $groupBy->renderer());

				$haveSplit = true;
			}
		}

		if ($this->_splitColumnMap)
		{
			$this->_splitSql = $splitSql;
		}
	}

	/**
	 * Prepares the GROUP BY clause.
	 */
	protected function _prepareGroupBy()
	{
		$sql = $this->_sql;

		foreach ($this->_groupBy AS $group) {
			$groupBy = $group->prepare($this, 'group', array(), $sql, $this->_resultHandler);
			if ($groupBy->hasValue()) {
				$printId = $sql->addSelectField($groupBy->printed());
				$sql->addGroupBy($groupBy->sql());

				if ($groupBy->printed() === $groupBy->sql()) {
					$groupId = $printId;
				} else {
					$groupId = $sql->addSelectField($groupBy->sql());
				}

				$this->_resultHandler->addGroupYColumn($groupBy->name(), $groupId, $printId, $groupBy->renderer());
			}
		}
	}

	/**
	 * Prepares the ORDER BY clause.
	 */
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

	/**
	 * Adds a select field to the SQL result
	 *
	 * @param string $select
	 * @param string|bool $alias If available, the name this column is aliased under
	 *
	 * @return int
	 */
	public function addSqlSelectField($select, $alias = false)
	{
		$selectFieldId = $this->_sql->addSelectField($select);

		if ($alias !== false) {
			$this->_fieldMap[$alias] = $selectFieldId;
		}

		return $selectFieldId;
	}

	/**
	 * Gets the SQL select field ID for the specified key. Used for alias lookup.
	 *
	 * @param string $key
	 *
	 * @return bool|integer
	 */
	public function getSqlSelectFieldId($key)
	{
		if (isset($this->_fieldMap[$key])) {
			return $this->_fieldMap[$key];
		} else {
			return false;
		}
	}

	/**
	 * Gets the entity repository for the from table.
	 *
	 * @return \Application\DeskPRO\EntityRepository\AbstractEntityRepository|bool
	 *
	 * @throws \Application\DeskPRO\Dpql\Exception
	 */
	public function getFromEntityRepository()
	{
		$table = strtolower($this->_from);
		if (!isset($this->_tableEntityMap[$table])) {
			return false;
		}

		$repositoryName = $this->_tableEntityMap[$table];
		$repository = App::getEntityRepository($repositoryName);

		if (!method_exists($repository, 'getTableName')) {
			throw new Exception("$repositoryName does not extend AbstractEntityRepository so cannot be queried.");
		} else {
			return $repository;
		}
	}

	/**
	 * Returns true if the value is non-empty (represents something printable to SQL)
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public function isSqlValue($input)
	{
		return strval($input) !== '';
	}

	/**
	 * Returns true if the stack of parent parts has forced date calculations to UTC
	 *
	 * @param array $stack
	 *
	 * @return bool
	 */
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

	/**
	 * Get the timezone offset for a function/column reference.
	 *
	 * @param array $stack
	 *
	 * @return int
	 */
	public function getTimezoneOffsetForFunction(array $stack)
	{
		if ($this->stackForcedUtc($stack)) {
			return 0;
		}

		return App::getCurrentPerson()->getTimezoneOffset() * 3600;
	}

	/**
	 * Quotes a string as a DPQL literal.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function quoteDpqlString($string)
	{
		$string = strtr($string, array("\\" => "\\\\", "'" => "\\'"));
		return "'$string'";
	}

	/**
	 * @param array $display
	 */
	public function setDisplay(array $display)
	{
		if (!$display) {
			$display = array('table');
		}

		$this->_display = array_unique($display);
	}

	/**
	 * @return array
	 */
	public function getDisplay()
	{
		return $this->_display;
	}

	/**
	 * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $select
	 */
	public function setSelect(array $select)
	{
		$this->_select = $select;
	}

	/**
	 * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart $select
	 */
	public function addSelect(AbstractPart $select)
	{
		$this->_select[] = $select;
	}

	/**
	 * @return \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]
	 */
	public function getSelect()
	{
		return $this->_select;
	}

	/**
	 * @param string $from
	 */
	public function setFrom($from)
	{
		$this->_from = $from;
	}

	/**
	 * @return string
	 */
	public function getFrom()
	{
		return $this->_from;
	}

	/**
	 * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart|null $where
	 */
	public function setWhere(AbstractPart $where = null)
	{
		$this->_where = $where;
	}

	/**
	 * @return \Application\DeskPRO\Dpql\Statement\Part\AbstractPart|null
	 */
	public function getWhere()
	{
		return $this->_where;
	}

	/**
	 * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $splitBy
	 */
	public function setSplitBy(array $splitBy)
	{
		$this->_splitBy = $splitBy;
	}

	/**
	 * @return \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]
	 */
	public function getSplitBy()
	{
		return $this->_splitBy;
	}

	/**
	 * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $groupBy
	 */
	public function setGroupBy(array $groupBy)
	{
		$this->_groupBy = $groupBy;
	}

	/**
	 * @return \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]
	 */
	public function getGroupBy()
	{
		return $this->_groupBy;
	}

	/**
	 * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $orderBy
	 */
	public function setOrderBy(array $orderBy)
	{
		$this->_orderBy = $orderBy;
	}

	/**
	 * @return \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]
	 */
	public function getOrderBy()
	{
		return $this->_orderBy;
	}

	/**
	 * @param int|null $amount
	 */
	public function setLimitAmount($amount)
	{
		$this->_limitAmount = $amount;
	}

	/**
	 * @return int|null
	 */
	public function getLimitAmount()
	{
		return $this->_limitAmount;
	}

	/**
	 * @param int|null $offset
	 */
	public function setLimitOffset($offset)
	{
		$this->_limitOffset = $offset;
	}

	/**
	 * @return int|null
	 */
	public function getLimitOffset()
	{
		return $this->_limitOffset;
	}

	/**
	 * Gets a DPQL query string from a list of parts.
	 *
	 * @param array $parts
	 *
	 * @return string
	 */
	public static function getQueryStringFromParts(array $parts)
	{
		if (empty($parts['from'])) {
			return '';
		} else {
			$offset = ($parts['offset'] ? " OFFSET $parts[offset]" : '');
			if ($parts['select'] === '') {
				$parts['select'] = 'COUNT()';
			}

			if (empty($parts['display'][0])) {
				$display = 'TABLE';
			} else {
				$parts['display'] = array_unique($parts['display']);
				$display = $parts['display'][0];
				if (!empty($parts['display'][1])) {
					$display .= ', ' . $parts['display'][1];
				}
			}

			return "DISPLAY $display"
				. "\nSELECT $parts[select]"
				. "\nFROM $parts[from]"
				. ($parts['where'] ? "\nWHERE $parts[where]" :'')
				. ($parts['splitBy'] ? "\nSPLIT BY $parts[splitBy]" :'')
				. ($parts['groupBy'] ? "\nGROUP BY $parts[groupBy]" :'')
				. ($parts['orderBy'] ? "\nORDER BY $parts[orderBy]" :'')
				. ($parts['limit'] ? "\nLIMIT $parts[limit]$offset" :'');
		}
	}
}