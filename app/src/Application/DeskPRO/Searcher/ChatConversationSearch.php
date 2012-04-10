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
*/

namespace Application\DeskPRO\Searcher;

use Application\DeskPRO\App;

use Orb\Util\Util;
use Orb\Util\Strings;
use Orb\Util\Arrays;

use Application\DeskPRO\Entity;

class ChatConversationSearch extends SearcherAbstract
{
	const TERM_ID                   = 'id';
	const TERM_AGENT_ID             = 'agent_id';
	const TERM_DEPARTMENT_ID        = 'department_id';
	const TERM_DATE_CREATED         = 'date_created';
	const TERM_STATUS               = 'status';

	protected $columns = 'chat_conversations.id';
	protected $groupBy = null;
	protected $limit = array('start' => null, 'limit' => null);
	protected $joins = array();


	/**
	 * Run the search and return an array of matching ID's.
	 *
	 * @return array
	 */
	public function getMatches()
	{
		$db = App::getDb();

		$ids = $db->fetchAllCol($this->getSql());

		return $ids;
	}



	public function setLimit($part, $amount)
	{
		$this->limit[$part] = $amount;
	}

	public function addJoin($join)
	{
		$this->joins[] = $join;
	}

	public function setColumns($columns)
	{
		$this->columns = $columns;
	}



	public function setGroupBy($group_by)
	{
		$this->groupBy = $group_by;
	}


	/**
	 * Get the SQL query that'll fetch the results
	 * @return string
	 */
	public function getSql()
	{
		$column_def = $this->columns;

		$sql = "SELECT $column_def FROM chat_conversations ";

		$parts = $this->getSqlParts();
		$order_by = $this->getOrderByPart();


		#------------------------------
		# Add joins
		#------------------------------

		foreach ($parts['joins'] as $j) {
			$sql .= "LEFT JOIN $j ";
		}

		if (is_array($order_by)) {
			list ($order_join, $order_by) = $order_by;

			$sql .= " $order_join ";
		}

		#------------------------------
		# Add wheres
		#------------------------------

		if(!$this->person->hasPerm('agent_tickets.view_unassigned')) {
			$parts['wheres'][] = 'agent_id IS NOT NULL';
		}

		if($this->person->getAgentPermissions()->getDisallowedDepartments('chat')) {
			$parts['wheres'][] = 'department_id NOT IN('.implode(',',$this->person->getAgentPermissions()->getDisallowedDepartments('chat')).')';
		}

		if (!$this->person->hasPerm('agent_tickets.view_others')) {
			$parts['where'][] = 'agent_id = ' . $this->person['id'];
		}

		if ($parts['wheres']) {
			$sql .= "WHERE ";
			$sql .= implode(" AND ", $parts['wheres']);
		}

		if($this->groupBy) {
			$sql .= ' GROUP BY ' . $this->groupBy;
		}

		$sql .= $order_by;

		$start = $this->limit['start'];
		$limit = $this->limit['limit'];

		if($limit !== null) {
			$sql .= " LIMIT $limit";
		}

		if($start !== null) {
			$sql .= " OFFSET $start";
		}

		return $sql;
	}



	/**
	 * Get the ORDER BY clause based on order info set.
	 *
	 * @return string
	 */
	public function getOrderByPart()
	{
		// Set a default if none
		if (!$this->order_by) {
			$this->order_by = array('chat_conversations.id', 'DESC');
		}

		list($type, $dir) = $this->order_by;

		$dir = strtoupper($dir);
		if ($dir != self::ORDER_ASC AND $dir != self::ORDER_DESC) {
			$dir = self::ORDER_DESC;
		}

		$order_by = '';

		switch ($type) {
			default:
				$order_by = " ORDER BY $type $dir";
				break;
		}

		return $order_by;
	}



	/**
	 * Get the SQL parts we need in the query.
	 *
	 * @return array
	 */
	public function getSqlParts()
	{
		$org_table = 'chat_conversations';

		$db = App::getDb();
		$tr = App::getTranslator();

		$wheres = array();
		$joins = $this->joins;

		foreach ($this->terms as $term => $info) {
			list($op, $choice) = $info;

			$term_id = null;

			$m = null;
			if (preg_match('#^(.*?)\[(.*?)\]$#', $term, $m)) {
				$term = $m[1];
				$term_id = $m[2];
			}

			switch ($term) {
				case self::TERM_ID:
					$wheres[] = $this->_choiceMatch('chat_conversations.id', $op, $children, true);
					break;
				case self::TERM_AGENT_ID:

					$info = $this->_normalizeAgentChoice($choice);
					$unassigned = $info['unassigned'];
					$agent_ids = $info['agent_ids'];
					$not_id = $info['not_id'];

					if ($unassigned) {
						$wheres[] = "chat_conversations.agent_id IS NULL";
					} else {
						if ($agent_ids) {
							$wheres[] = $this->_choiceMatch("chat_conversations.agent_id", $op, $agent_ids, true);
						}

						if ($not_id) {
							$wheres[] = "chat_conversations.agent_id != " . $not_id;
						}
					}
					break;
				case self::TERM_DEPARTMENT_ID:
					$children[] = $choice;
					$children = App::getEntityRepository('DeskPRO:Department')->getIdsInTree($choice, true);
					$wheres[] = $this->_choiceMatch('chat_conversations.department_id', $op, $children, true);
					break;

				case self::TERM_DATE_CREATED:
					$this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.date_created'), $op, $choice);
					$wheres[] = $this->_dateMatch('chat_conversations.date_created', $op, $choice);
					break;

				case self::TERM_STATUS:
					$wheres[] = $this->_stringMatch('chat_conversations.status', $op, $choice);
					break;
			}
		}

		return array(
			'joins' => $joins,
			'wheres' => $wheres
		);
	}


}