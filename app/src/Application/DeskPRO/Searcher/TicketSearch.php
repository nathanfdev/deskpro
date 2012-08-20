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
use Application\DeskPRO\Entity\Ticket;

class TicketSearch extends SearcherAbstract
{
	const TERM_ID                        = 'id';
	const TERM_DEPARTMENT                = 'department';
	const TERM_CATEGORY                  = 'category';
	const TERM_PRODUCT                   = 'product';
	const TERM_AGENT                     = 'agent';
	const TERM_AGENT_TEAM                = 'agent_team';
	const TERM_STATUS                    = 'status';
	const TERM_HIDDEN_STATUS             = 'hidden_status';
	const TERM_WORKFLOW                  = 'workflow';
	const TERM_PRIORITY                  = 'priority';
	const TERM_SUBJECT                   = 'subject';
	const TERM_ORGANIZATION              = 'organization';
	const TERM_LANGUAGE                  = 'language';
	const TERM_PARTICIPANT               = 'participant';
	const TERM_LABEL                     = 'label';
	const TERM_TICKET_FIELD              = 'ticket_field';
	const TERM_DATE_CREATED              = 'date_created';
	const TERM_DATE_RESOLVED             = 'date_resolved';
	const TERM_DATE_CLOSED               = 'date_closed';
	const TERM_DATE_LAST_USER_REPLY      = 'date_last_user_reply';
	const TERM_DATE_LAST_AGENT_REPLY     = 'date_last_agent_reply';
	const TERM_URGENCY                   = 'urgency';
	const TERM_USER_WAITING              = 'user_waiting';
	const TERM_TOTAL_USER_WAITING        = 'total_user_waiting';
	const TERM_AGENT_WAITING             = 'agent_waiting';
	const TERM_ARCHIVE_SEARCH            = 'archive_search';
	const TERM_DELETED                   = 'deleted';
	const TERM_CREATION_SYSTEM           = 'creation_system';
	const TERM_RECEIVING_GATEWAY         = 'gateway_account';
	const TERM_GATEWAY_ADDRESS           = 'email_gateway_address';
	const TERM_HOLD                      = 'is_hold';
	const TERM_FLAGGED                   = 'flagged';
	const TERM_TEXT                      = 'text';

	/**
	 * True to search in the non-search tables (aka all tickets not just active)
	 * @var bool
	 */
	protected $is_archive = false;

	/**
	 * @var PersonSearch
	 */
	protected $person_search = null;

	/**
	 * From getSqlParts()
	 * @var array
	 */
	protected $sql_parts = null;

	/**
	 * Summary of terms in phrases
	 * @var array
	 */
	protected $summary = array();

	/**
	 * Summary of sorting in phrases
	 * @var array
	 */
	protected $order_summary = array();

	/**
	 * An array of fields these search terms are affected by.
	 * Used in ListUpdater to determine if a filter needs changing on the client.
	 *
	 * @var array
	 */
	protected $affected_fields = array();

	/**
	 * An array of search terms that are specific, as in only allow a single
	 * value (so not ranges or IN() types). For example, a single department or organization
	 *
	 * @return array
	 */
	protected $specific_fields = array();

    /**
     * Amount to limit results by (unless pageinfo is provided).
     *
     * @var string
     */
    protected $limit = '1000';

	/**
	 * @var array
	 */
	protected $add_raw_wheres = array();

	/**
	 * @var array
	 */
	protected $add_raw_joins = array();

	/**
	 * @var array
	 */
	protected $add_raw_selects = array();

	/**
	 * @var bool
	 */
	protected $is_filter_search = false;

	public $_last_sql = null;

	/**
	 * Set a set of person search terms.
	 *
	 * @param PersonSearch $person_search
	 */
	public function setPersonSearch(PersonSearch $person_search)
	{
		$this->person_search = $person_search;
	}



	/**
	 * Search old (closed) tickets that are archived (aka not in the search tables).
	 */
	public function enableArchiveSearch()
	{
		$this->is_archive = true;
	}


	/**
	 * Enables only non-hidden or closed tickets.
	 */
	public function enableFilterSearch()
	{
		$this->is_filter_search = true;
	}


	/**
	 * Are we using archive mode?
	 *
	 * @return bool
	 */
	public function isArchiveSearch()
	{
		return $this->is_archive;
	}

    /**
     * Set the amount to limit results by (by default).
     *
     * @param $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

	/**
	 * Get the summary of crtiera
	 *
	 * @return array
	 */
	public function getSummary()
	{
		$this->getSqlParts();

		$summary = $this->summary;
		if ($this->person_search) {
			$summary = array_merge($summary, $this->person_search->getSummary());
		}

		return $summary;
	}


	/**
	 * Get the order-by summary
	 *
	 * @return array
	 */
	public function getOrderBySummary()
	{
		$this->getOrderByPart();

		return $this->order_summary;
	}


	/**
	 * Get specific fields in this search
	 *
	 * @return array
	 */
	public function getSpecificFields()
	{
		$this->getSqlParts();
		return $this->specific_fields;
	}


	/**
	 * @return array
	 */
	public function getAffectedFields()
	{
		$this->getSqlParts();
		return array_unique($this->affected_fields, SORT_STRING);
	}


	/**
	 * Check an array of fields to see if this searcher has any of them
	 *
	 * @param array $fields
	 * @return bool
	 */
	public function hasAnyAffectedFields(array $fields)
	{
		$this->getSqlParts();

		$affected_fields = $this->getAffectedFields();
		foreach ($fields as $f) {
			if (in_array($f, $affected_fields)) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Run the search and return an array of matching ID's.
	 *
	 * @param int $limit
	 * @return array
	 */
	public function getMatches(array $pageinfo = null)
	{
		$sql = $this->getSql($pageinfo);
		$this->getLogger()->logDebug("Search Query: " . $sql);
		$time = microtime(true);

		$db = App::getDb();
		$ticket_ids = $db->fetchAllCol($sql);

		$this->getLogger()->logDebug("-- Time: " . sprintf("%.5f", microtime(true) - $time));
		$this->getLogger()->logDebug("-- Count: " . count($ticket_ids));
		$this->getLogger()->logDebug("-- IDs: " . implode(', ', $ticket_ids));

		return $ticket_ids;
	}


	/**
	 * Run the search and get the count
	 *
	 * @param int $limit Null for no limit
	 */
	public function getCount($limit = 1000)
	{
		$ticket_parts = $this->getSqlParts();
		$user_parts = null;
		if ($this->person_search) {
			$user_parts = $this->person_search->getSqlParts();
		}

		$where = '';

		if ($this->isArchiveSearch()) {
			$table = 'tickets';
		} else {
			$table = 'tickets_search_active';
		}

		$select = '';
		if ($this->add_raw_selects) {
			$select = ', ' . implode(', ', $this->add_raw_selects);
		}
		$sql = "SELECT COUNT(*) FROM $table AS tickets ";

		#------------------------------
		# Standard for permissions
		#------------------------------

		if ($this->person AND $this->person['is_agent']) {

			$where_perm = array();
			$where = '((';

			if ($this->person->getDisallowedDepartments()) {
				$where_perm[] = "tickets.department_id NOT IN (" . implode(',', $this->person->getDisallowedDepartments()) . ")";
			}

			if (!$this->person->hasPerm('agent_tickets.view_unassigned')) {
				$where_perm[] = 'tickets.agent_id IS NOT NULL';
			}

			if (!$this->person->hasPerm('agent_tickets.view_others')) {
				$part = array();
				$part[] = "tickets.agent_id = {$this->person['id']}";
				if ($this->person->getAgentTeamIds()) {
					$part[] = "tickets.agent_team_id IN (" . implode(',', $this->person->getAgentTeamIds()) . ")";
				}

				$where_perm[] = '(' . implode(' OR ', $part) . ')';
			}

			if (!$where_perm) {
				$where_perm[] = '1';
			}

			$where = '((' . implode(' AND ', $where_perm) . ') OR (';

			$ticket_parts['joins'][] = array('tickets_participants_perm', "LEFT JOIN tickets_participants AS tickets_participants_perm ON (tickets_participants_perm.ticket_id = tickets.id)");
			$where .= "tickets.agent_id = {$this->person['id']} OR ";
			if ($this->person->getAgentTeamIds()) {
				$where .= "tickets.agent_team_id IN (" . implode(',', $this->person->getAgentTeamIds()) . ") OR ";
			}

			$where .= "tickets_participants_perm.person_id = {$this->person->id})) AND ";
		}


		#------------------------------
		# Add joins
		#------------------------------

		foreach ($ticket_parts['joins'] as $j) {
			if (is_array($j)) {
				$sql .= $j[1] . " ";
			} else {
				$sql .= "LEFT JOIN $j ON $j.ticket_id = tickets.id ";
			}
		}

		if ($user_parts) {
			$sql .= "LEFT JOIN people ON (people.id = tickets.person_id) ";
		}

		if ($user_parts AND $user_parts['joins']) {

			foreach ($user_parts['joins'] as $j) {
				if (is_array($j)) {
					$sql .= $j[1] . " ";
				} else {
					$sql .= "LEFT JOIN $j ON $j.person_id = people.id ";
				}
			}
		}

		if ($this->add_raw_joins) {
			$sql .= implode(' ', $this->add_raw_joins);
		}

		#------------------------------
		# Add wheres
		#------------------------------

		if (!empty($ticket_parts['wheres'])) {
			$where .= implode(" AND ", $ticket_parts['wheres']);
		}
		if (!empty($user_parts['wheres'])) {
			$where .= " AND " . implode(" AND ", $user_parts['wheres']);
		}

		if ($this->add_raw_wheres) {
			$where .= " AND " . implode(' AND ', $this->add_raw_wheres);
		}

		if ($this->is_filter_search) {
			$where .= " AND tickets.status NOT IN ('closed', 'hidden') ";
		}

		if ($where) {
			$sql .= " WHERE $where";
		}

		if ($limit) {
			$sql .= "LIMIT $limit";
		}

		$this->getLogger()->logDebug("Search Count Query: " . $sql);
		$time = microtime(true);

		$db = App::getDb();
		$result = $db->fetchColumn($sql);

		$this->getLogger()->logDebug("-- Time: " . sprintf("%.5f", microtime(true) - $time));
		$this->getLogger()->logDebug("-- Count: " . $result);

		return $result;
	}



	/**
	 * Get the SQL query that'll fetch the results
	 * @return string
	 */
	public function getSql(array $pageinfo = null)
	{
		$ticket_parts = $this->getSqlParts();
		$user_parts = null;
		if ($this->person_search) {
			$user_parts = $this->person_search->getSqlParts();
		}

		$order_by = $this->getOrderByPart();

		$where = '';

		if ($this->isArchiveSearch()) {
			$table = 'tickets';
		} else {
			$table = 'tickets_search_active';
		}

		$select = '';
		if ($this->add_raw_selects) {
			$select = ', ' . implode(', ', $this->add_raw_selects);
		}
		$sql = "SELECT tickets.id $select FROM $table AS tickets ";

		#------------------------------
		# Standard for permissions
		#------------------------------

		if ($this->person AND $this->person['is_agent']) {

			$where_perm = array();
			$where = '((';

			if ($this->person->getDisallowedDepartments()) {
				$where_perm[] = "tickets.department_id NOT IN (" . implode(',', $this->person->getDisallowedDepartments()) . ")";
			}

			if (!$this->person->hasPerm('agent_tickets.view_unassigned')) {
				$where_perm[] = 'tickets.agent_id IS NOT NULL';
			}

			if (!$this->person->hasPerm('agent_tickets.view_others')) {
				$part = array();
				$part[] = "tickets.agent_id = {$this->person['id']}";
				if ($this->person->getAgentTeamIds()) {
					$part[] = "tickets.agent_team_id IN (" . implode(',', $this->person->getAgentTeamIds()) . ")";
				}

				$where_perm[] = '(' . implode(' OR ', $part) . ')';
			}

			if (!$where_perm) {
				$where_perm[] = '1';
			}

			$where = '((' . implode(' AND ', $where_perm) . ') OR (';

			$ticket_parts['joins'][] = array('tickets_participants_perm', "LEFT JOIN tickets_participants AS tickets_participants_perm ON (tickets_participants_perm.ticket_id = tickets.id)");
			$where .= "tickets.agent_id = {$this->person['id']} OR ";
			if ($this->person->getAgentTeamIds()) {
				$where .= "tickets.agent_team_id IN (" . implode(',', $this->person->getAgentTeamIds()) . ") OR ";
			}

			$where .= "tickets_participants_perm.person_id = {$this->person->id})) AND ";
		}


		#------------------------------
		# Add joins
		#------------------------------

		foreach ($ticket_parts['joins'] as $j) {
			if (is_array($j)) {
				$sql .= $j[1] . " ";
			} else {
				$sql .= "LEFT JOIN $j ON $j.ticket_id = tickets.id ";
			}
		}

		if ($user_parts) {
			$sql .= "LEFT JOIN people ON (people.id = tickets.person_id) ";
		}

		if ($user_parts AND $user_parts['joins']) {

			foreach ($user_parts['joins'] as $j) {
				if (is_array($j)) {
					$sql .= $j[1] . " ";
				} else {
					$sql .= "LEFT JOIN $j ON $j.person_id = people.id ";
				}
			}
		}

		if (is_array($order_by)) {
			list ($order_join, $order_by) = $order_by;

			$sql .= " $order_join ";
		}

		if ($this->add_raw_joins) {
			$sql .= implode(' ', $this->add_raw_joins);
		}

		#------------------------------
		# Add wheres
		#------------------------------

		if (!empty($ticket_parts['wheres'])) {
			$where .= implode(" AND ", $ticket_parts['wheres']);
		}
		if (!empty($user_parts['wheres'])) {
			$where .= " AND " . implode(" AND ", $user_parts['wheres']);
		}

		if ($this->add_raw_wheres) {
			$where .= " AND " . implode(' AND ', $this->add_raw_wheres);
		}

		if ($this->is_filter_search) {
			$where .= " AND tickets.status NOT IN ('closed', 'hidden') ";
		}

		if ($where) {
			$sql .= " WHERE $where";
		}

		$sql .= " GROUP BY tickets.id ";
		$sql .= $order_by;

		if ($pageinfo) {
			// A null limit means no limit :o
			if ($pageinfo['limit'] !== null) {
				$sql .= " LIMIT {$pageinfo['offset']}, {$pageinfo['limit']} ";
			}
		} else {
            if($this->limit) {
			    $sql .= ' LIMIT '.$this->limit;
            }
		}

		$this->_last_sql = $sql;

		return $sql;
	}



	/**
	 * Get the ORDER BY clause based on order info set.
	 *
	 * @return string
	 */
	public function getOrderByPart()
	{
		if (!$this->order_by AND $this->person_search AND $this->person_search->getOrderBy()) {
			return $this->person_search->getOrderByPart();
		}

		// Set a default if none
		if (!$this->order_by) {
			$this->order_by = array('ticket.urgency', 'DESC');
		}

		list($type, $dir) = $this->order_by;

		$dir = strtoupper($dir);
		if ($dir != self::ORDER_ASC AND $dir != self::ORDER_DESC) {
			$dir = self::ORDER_DESC;
		}

		$r_dir = $dir == self::ORDER_ASC ? 'DESC' : 'ASC';

		$term_id = null;
		$m = null;
		if (preg_match('#^(.*?)\[(.*?)\]$#', $type, $m)) {
			$type = $m[1];
			$term_id = $m[2];
		}


		$order_by = '';
        $tr = App::getTranslator();

		switch ($type) {
			case 'ticket.urgency':
				if($this->needsUrgency()) {
					$order_by = "ORDER BY status = 'awaiting_agent' $dir, tickets.urgency $dir, tickets.date_user_waiting $r_dir";
				}
				else {
					$order_by = "ORDER BY tickets.urgency $dir, tickets.id $dir";
				}

				$this->order_summary = $tr->phrase('agent.general.urgency');
				break;

			case 'ticket.date_created':
				$order_by = "ORDER BY tickets.id $dir";
				$this->order_summary = $tr->phrase('agent.general.date_opened');
				break;

			case 'ticket.priority':
				$pris = App::getEntityRepository('DeskPRO:TicketPriority')->getIdsInOrder();
				if ($pris) {
					$order_by = "ORDER BY FIELD(tickets.priority_id, " . implode(',', $pris) . ") $dir, tickets.id $dir";
				} else {
					$order_by = "ORDER BY tickets.priority_id $dir, tickets.id $dir";
				}
				$this->order_summary = $tr->phrase('agent.general.priority');
				break;

			case 'ticket.date_resolved':
				$this->order_summary = $tr->phrase('agent.general.date_resolved');
				$order_by = "ORDER BY tickets.date_resolved $dir";
				break;

			case 'ticket.date_closed':
				$this->order_summary = $tr->phrase('agent.general.date_opened');
				$order_by = "ORDER BY tickets.date_closed $dir";
				break;

			case 'ticket.last_activity':
				$this->order_summary = $tr->phrase('agent.general.date_of_last_user_reply');
				$order_by = "ORDER BY tickets.date_last_user_reply $dir";
				break;

            case 'ticket.total_user_waiting':
                $this->order_summary = $tr->phrase('agent.general.total_time_waiting');
                $order_by = "ORDER BY tickets.total_user_waiting $dir";
                break;

            case 'ticket.date_user_waiting':
                $this->order_summary = $tr->phrase('agent.general.time_waiting');
                $order_by = "ORDER BY tickets.date_user_waiting $dir";
                break;

			case 'ticket.organization':
				$this->order_summary = $tr->phrase('agent.general.organization_name');
				$order_by = array(
					"INNER JOIN organizations AS sort_table ON (sort_table.id = tickets.organization_id)",
					"ORDER BY sort_table.name $dir"
				);
				break;

			case 'ticket.ticket_field':
				$field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($term_id);
				if (!$field) break;

				$this->order_summary = $field['title'];

				$search_type = $field->getHandler()->getSearchType();

				switch ($search_type) {
					case 'input':
					case 'value':
						$order_by = arary(
							"INNER JOIN custom_data_ticket AS sort_table ON (sort_table.ticket_id = tickets.id AND sort_table.id = $term_id)",
							"ORDER BY sort_table.$search_type $dir"
						);
						break;
				}
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
		if ($this->sql_parts !== null) return $this->sql_parts;

		$tickets_table = 'tickets';

		$db = App::getDb();
		$tr = App::getTranslator();

		$wheres = array();
		$joins = array();

		// If we dont set a status, we will automatically
		// exclude 'hidden' tickets
		$set_status = false;

		foreach ($this->terms as $term => $info) {

			if (!$info || !is_array($info)) continue;

			$join_id = Util::requestUniqueId();
			$join_name = "j_$join_id";

			list($op, $choice) = $info;

			$term_id = null;

			// $term of ticket_field[12] becomes $term=ticket_field, $term_id=12
			$m = null;
			if (preg_match('#^(.*?)\[(.*?)\]$#', $term, $m)) {
				$term = $m[1];
				$term_id = $m[2];
			}

			// The term handlers below that only accept single values
			// will use $choice as a single value for brevity
			if (is_array($choice) AND count($choice) == 1) {
				$choice = Arrays::getFirstItem($choice);
			}

			if ($term_id) {
				$this->getLogger()->logDebug(sprintf("Term: %s[%s] %s %s", $term, $term_id, $op, \DeskPRO\Kernel\KernelErrorHandler::varToString($choice)));
			} else {
				$this->getLogger()->logDebug(sprintf("Term: %s %s %s", $term, $op, \DeskPRO\Kernel\KernelErrorHandler::varToString($choice)));
			}

			switch ($term) {
				case self::TERM_ID:
					$this->enableArchiveSearch();
					if ($op == self::OP_IS) {
						$wheres[] = "$tickets_table.id IN (" . implode(',', (array)$choice) . ")";
					} else {
						$wheres[] = $this->_rangeMatch("$tickets_table.id", $op, $choice, true);
					}
					$this->summary[] = $this->_rangeSummary($tr->phrase('agent.general.id'), $op, $choice);
					break;

				case self::TERM_TEXT:
					if (is_array($choice)) {
						$choice = array_pop($choice);
					}

					$joins[] = array(
						'content_search',
						"LEFT JOIN content_search AS $join_name ON ($join_name.object_type = 'ticket' AND $join_name.object_id = tickets.id)"
					);

					$wheres[] = "MATCH ($join_name.content) AGAINST (" . App::getDb()->quote($choice) . ")";

					$this->summary[] = "Ticket content matches: " . $choice;
					break;

				case self::TERM_ARCHIVE_SEARCH:
					if ($choice) {
						$this->enableArchiveSearch();
					}
					break;
				case self::TERM_DEPARTMENT:

					$this->affected_fields[] = 'ticket.department_id';
					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.general.department'), $op, $choice, function($choice) {
						$titles = App::getDataService('Department')->getNames((array)$choice);
						return $titles;
					});

					if ($choice && (!is_array($choice) || !in_array('0', $choice))) {
						$choice = (array)$choice;
						foreach ($choice as $id) {
							$choice = array_merge($choice, App::getDataService('Department')->getIdsInTree($id, true));
						}
						$choice = array_unique($choice, \SORT_NUMERIC);
					}

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_DEPARTMENT;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.department_id", $op, $choice, true);

					break;
				case self::TERM_DELETED:
					$this->affected_fields[] = 'ticket.status';
					$this->affected_fields[] = 'ticket.hidden_status';

					$set_status = true;
					$this->summary[] = $tr->phrase('agent.tickets.ticket_is_deleted');
					$wheres[] = $this->_choiceMatch("$tickets_table.status", self::OP_IS, 'hidden');
					$wheres[] = $this->_choiceMatch("$tickets_table.hidden_status", self::OP_IS, 'deleted');

					$this->enableArchiveSearch();

					break;
				case self::TERM_CATEGORY:
					$this->affected_fields[] = 'ticket.category_id';
					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.general.category'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:TicketCategory')->getNames((array)$choice);
						return $titles;
					});

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_CATEGORY;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.category_id", $op, $choice, true);
					break;
				case self::TERM_PRODUCT:
					$this->affected_fields[] = 'ticket.product_id';
					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.general.product'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:Product')->getNames((array)$choice);
						return $titles;
					});

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_PRODUCT;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.product_id", $op, $choice, true);
					break;
				case self::TERM_PRIORITY:
					$this->affected_fields[] = 'ticket.priority_id';

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_PRIORITY;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.priority_id", $op, $choice, true);
					break;
				case self::TERM_URGENCY:
					$this->affected_fields[] = 'ticket.urgency';
					$this->summary[] = $this->_rangeSummary($tr->phrase('agent.general.urgency'), $op, $choice);
					$wheres[] = $this->_rangeMatch("$tickets_table.urgency", $op, $choice);
					break;
				case self::TERM_DATE_CREATED:
					$this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.general.date_created'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_created", $op, $choice);
					break;
				case self::TERM_DATE_RESOLVED:
					$this->affected_fields[] = 'ticket.date_resolved';
					$this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.general.date_resolved'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_resolved", $op, $choice);
					$wheres[] = $this->_choiceMatch("$tickets_table.status", $op, array('resolved'));
					break;
				case self::TERM_DATE_CLOSED:
					$this->affected_fields[] = 'ticket.date_closed';
					$this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.general.date_closed'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_closed", $op, $choice);
					$wheres[] = $this->_choiceMatch("$tickets_table.status", $op, array('closed'));
					break;
				case self::TERM_DATE_LAST_USER_REPLY:
					$this->affected_fields[] = 'ticket.date_last_user_reply';
					$this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.general.date_of_last_user_reply'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_last_user_reply", $op, $choice);
					break;
				case self::TERM_DATE_LAST_AGENT_REPLY:
					$this->affected_fields[] = 'ticket.date_last_agent_reply';
					$this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.general.date_of_last_agent_reply'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_last_agent_reply", $op, $choice);
					break;
				case self::TERM_WORKFLOW:
					$this->affected_fields[] = 'ticket.workflow_id';
					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.general.workflow'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:TicketWorkflow')->getNames((array)$choice);
						return $titles;
					});

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_WORKFLOW;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.workflow_id", $op, $choice, true);
					break;
				case self::TERM_LANGUAGE:
					$this->affected_fields[] = 'ticket.language_id';
					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_LANGUAGE;
					}

					if(is_array($choice)) {
						$choice = array_pop($choice);
					}

					if ($choice == App::getSetting('core.default_language_id')) {
						$wheres[] = "(" . $this->_choiceMatch("$tickets_table.language_id", $op, $choice, true) . " OR " . $this->_choiceMatch("$tickets_table.language_id", $op, 0, true) . ")";
					} else {
						$wheres[] = $this->_choiceMatch("$tickets_table.language_id", $op, $choice, true);
					}
					break;
				case self::TERM_AGENT:
					$this->affected_fields[] = 'ticket.agent_id';

					$info = $this->_normalizeAgentChoice($choice);
					$unassigned = $info['unassigned'];
					$agent_ids = $info['agent_ids'];
					$not_id = $info['not_id'];

					if ($unassigned) {
						$this->summary[] = $this->_choiceSummary($tr->phrase('agent.general.agent'), $op, $tr->phrase('agent.general.agent'));
						$wheres[] = "$tickets_table.agent_id IS NULL";
					} else {
						if ($agent_ids) {
							$this->summary[] = $this->_choiceSummary($tr->phrase('agent.general.agent'), $op, $agent_ids, function($choice) {
								$titles = App::getEntityRepository('DeskPRO:Person')->getAgentNames((array)$choice);
								return $titles;
							});

							if (count($agent_ids) == 1) {
								$this->specific_fields[] = self::TERM_AGENT;
							}

							$wheres[] = $this->_choiceMatch("$tickets_table.agent_id", $op, $agent_ids, true);
						}

						if ($not_id) {
							$this->summary[] = $tr->phrase('agent.general.agent_is_not_me');
							$wheres[] = "$tickets_table.agent_id != " . $not_id;
						}
					}
					break;
				case self::TERM_AGENT_TEAM:
					$this->affected_fields[] = 'ticket.agent_team_id';

					$info = $this->_normalizeAgentTeamChoice($choice);
					$team_ids = $info['team_ids'];
					$not_ids = $info['not_ids'];
					$no_team = $info['no_team'];

					if ($no_team) {
						$wheres[] = "$tickets_table.agent_team_id IS NULL";
						$this->summary[] = $this->_choiceSummary($tr->phrase('agent.general.agent_team'), $op, $tr->phrase('agent.general.agent_team'));

					} else {
						if ($team_ids) {
							$this->summary[] = $this->_choiceSummary($tr->phrase('agent.general.agent_team'), $op, $team_ids, function($choice) {
								$titles = App::getEntityRepository('DeskPRO:AgentTeam')->getTeamNames((array)$choice);
								return $titles;
							});

							if (count($choice) == 1) {
								$this->specific_fields[] = self::TERM_AGENT_TEAM;
							}

							$wheres[] = $this->_choiceMatch("$tickets_table.agent_team_id", $op, $team_ids, true);
						}

						if ($not_ids) {
							$this->summary[] = $this->_choiceSummary($tr->phrase('agent.general.agent_team'), 'not', $not_ids, function($choice) {
								$titles = App::getEntityRepository('DeskPRO:AgentTeam')->getTeamNames((array)$choice);
								return $titles;
							});

							$wheres[] = $this->_choiceMatch("$tickets_table.agent_team_id", 'not', $team_ids, true);
						}
					}
					break;
				case self::TERM_STATUS:

					$this->affected_fields[] = 'ticket.status';
					$set_status = true;

					$show_status = array();
					$hidden_status = array();

					$choice_str = array();

					foreach ((array)$choice as $c) {
						if (strpos($c, '.') !== false) {
							list ($status, $hstatus) = explode('.', $c, 2);
							$hidden_status[] = $hstatus;
							$choice_str[] = $tr->phrase('agent.tickets.hidden_status_' . $c);
							$this->enableArchiveSearch();
						} else {
							$show_status[] = $show_status;
							$choice_str[] = $tr->phrase('agent.tickets.status_' . $c);
							if ($c != 'awaiting_agent' && $c != 'awaiting_user' && $c != 'resolved') {
								$this->enableArchiveSearch();
							}
						}
					}

					$choice_str = implode(' or ', $choice_str);
					$this->summary[] = 'Status is ' . $choice_str;

					$w = '(';
					if ($show_status) {
						$w .= '(';
						$w .= $this->_choiceMatch("$tickets_table.status", $op, $choice);
						$w .= ')';
					} else {
						$w .= '(';
						$w .= $this->_choiceMatch("$tickets_table.hidden_status", $op, $hidden_status);
						$w .= ')';
					}
					$w .= ')';

					$wheres[] = $w;
					break;
				case self::TERM_HIDDEN_STATUS:
					$this->affected_fields[] = 'ticket.hidden_status';

					$choice_str = array();
					foreach ((array)$choice as $c) {
						$choice_str[] = $tr->phrase('agent.tickets.hidden_status_' . $c);
					}
					$choice_str = implode(', ', $choice_str);

					$this->summary[] = $tr->phrase('agent.general.x_is_y', array('field' => $tr->phrase('agent.general.is_not_x'), 'value' => $choice_str));

					$wheres[] = $this->_choiceMatch("$tickets_table.hidden_status", $op, $choice);
					$this->enableArchiveSearch();

					break;
				case self::TERM_HOLD:

					$this->affected_fields[] = 'ticket.is_hold';

					// Op is irrelevant. or, it's always "is", and choice is yes/no

					if ($choice) {
						$wheres[] = "tickets.is_hold = 1";
					} else {
						$wheres[] = "tickets.is_hold = 0";
					}

					$this->summary[] = $tr->phrase('agent.general.is_not_x', array('field' => 'on hold'));

					break;
				case self::TERM_ORGANIZATION:
					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.general.organization'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:Organization')->getOrganizationNames((array)$choice);
						return $titles;
					});

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_ORGANIZATION;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.organization_id", $op, $choice, true);
					break;
				case self::TERM_PARTICIPANT:
					$this->affected_fields[] = 'ticket.participants';
					$joins[] = 'tickets_participants';
					$field = 'tickets_participants.person_id';

					$choice_info = $this->_normalizeAgentChoice($choice);
					if (!empty($choice_info['agent_ids'])) {
						$choice = $choice_info['agent_ids'];
					} else {
						continue;
					}

					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.general.followers'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:Person')->getAgentNames((array)$choice);
						return $titles;
					}, true);

					$wheres[] = $this->_choiceMatch($field, $op, $choice);
					break;
				case self::TERM_SUBJECT:
					$this->affected_fields[] = 'ticket.subject';
					$field = 'tickets.subject';
					if (!$this->is_archive) {
						$joins[] = array(
							'tickets_search_subject',
							"LEFT JOIN tickets_search_subject AS $join_name ON ($join_name.id = tickets.id)"
						);
						$field = "$join_name.subject";
					}

					if ($op == self::OP_IS || $op == self::OP_CONTAINS) {
						$this->summary[] = $tr->phrase('agent.general.x_include_y', array('field' => $tr->phrase('agent.general.subject'), 'value' => $choice));
					} else {
						$this->summary[] = $tr->phrase('agent.general.x_is_not_y', array('field' => $tr->phrase('agent.general.subject'), 'value' => $choice));
					}
					$wheres[] = $this->_stringMatch($field, $op, $choice);
					break;

				case self::TERM_FLAGGED:

					$this->affected_fields[] = 'tickets_flagged';
					$joins[] = 'tickets_flagged';

					$color = $choice;
					if ($color == 'any') {
						$this->summary[] = "Flagged";
						$wheres[] = 'tickets_flagged.person_id = '. $this->person->id;
					} else {
						$this->summary[] = "Flagged with color {$color}";
						$wheres[] = '(tickets_flagged.person_id = '. $this->person->id . ' AND ' . $this->_stringMatch('tickets_flagged.color', $op, $color) . ')';
					}

					break;

				case self::TERM_LABEL:
					$this->affected_fields[] = 'ticket.labels';
					$this->_normalizeOpAndChoice($op, $choice);

					$choices_in = array();
					if (is_array($choice)) {
						foreach ((array)$choice as $c) {
							$choices_in[] = $db->quote($c);
						}
						$choices_in = implode(',', $choices_in);
					}

					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.general.label'), $op, $choice);

					switch ($op) {
						case self::OP_IS:
							$joins[] = array(
								'labels_tickets',
								"LEFT JOIN labels_tickets AS $join_name ON ($join_name.ticket_id = tickets.id)"
							);
							$wheres[] = "$join_name.label = " . $db->quote($choice);
							break;
						case self::OP_NOT:
							$joins[] = array(
								'labels_tickets',
								"LEFT JOIN labels_tickets AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.label = '.$db->quote($choice).')"
							);
							$wheres[] = "$join_name.ticket_id IS NULL";
							break;
						case self::OP_CONTAINS:
							$joins[] = array(
								'labels_tickets',
								"LEFT JOIN labels_tickets AS $join_name ON ($join_name.ticket_id = tickets.id)"
							);
							$wheres[] = "$join_name.label IN ($choices_in)";
							break;

						case self::OP_NOTCONTAINS:
							$joins[] = array(
								'labels_tickets',
								"LEFT JOIN labels_tickets AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.label IN ($choices_in)"
							);
							$wheres[] = "$join_name.ticket_id IS NULL";
							break;
					}
					break;

				case self::TERM_TICKET_FIELD:
					$field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($term_id);
					if (!$field) break;

					$this->affected_fields[] = 'ticket.custom_data_ticket_' . $field['id'];

					$search_type = $field->getHandler()->getSearchType();

					if (isset($choice['custom_fields']['field_' . $term_id])) {
						$choice = $choice['custom_fields']['field_' . $term_id];
					}

					switch ($search_type) {
						case 'input':
						case 'value':

							if (is_array($choice)) {
								$choice = array_pop($choice);
							}

							if ($op == self::OP_IS) {
								$this->summary[] = $tr->phrase('agent.general.x_is_y', array('field' => $field['title'], 'value' => $choice));
							} else {
								$this->summary[] = $tr->phrase('agent.general.x_is_not_y', array('field' => $field['title'], 'value' => $choice));
							}

							$joins[] = array(
								'custom_data_ticket',
								"LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND custom_data_ticket_$join_id.field_id = $term_id)"
							);

							$field = 'custom_data_ticket_'.$join_id.'.'.$search_type;
							switch ($op) {
								case self::OP_IS:
									$wheres[] = "$field = " . $db->quote($choice);
									break;
								case self::OP_NOT:
									$wheres[] = "$field != " . $db->quote($choice);
									break;
								case self::OP_CONTAINS:
								case self::OP_NOTCONTAINS:
									$op = 'LIKE';
									if ($op == self::OP_NOTCONTAINS) $op = 'NOT LIKE';
									$wheres[] = "$field $op " . $db->quote('%'.$choice.'%');
									break;
							}

							$this->summary[] = "";

							break;

						case 'id':
							$join_id = Util::requestUniqueId();
							$choices_in = array();
							$choice = (array)$choice;
							foreach ($choice as $c) {
								$choices_in[] = (int)$c;
							}
							$choices_in = implode(',', $choices_in);

							$choice_str = array();
							foreach ($field->children as $child) {
								if (in_array($child['id'], $choice)) {
									$choice_str[] = $child['title'];
								}
							}
							$choice_str = implode(', ', $choice_str);

							if ($op == self::OP_IS OR $op== self::OP_CONTAINS) {
								$this->summary[] = $tr->phrase('agent.general.x_is_y', array('field' => $field['title'], 'value' => $choice_str));
							} else {
								$this->summary[] = $tr->phrase('agent.general.x_is_not_y', array('field' => $field['title'], 'value' => $choice_str));
							}

							$field = 'custom_data_ticket_'.$join_id.'.field_id';
							switch ($op) {
								case self::OP_CONTAINS:
								case self::OP_IS:
									$joins[] = array(
										'custom_data_ticket',
										"LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND $field IN ($choices_in))"
									);
									$wheres[] = "custom_data_ticket_$join_id.id IS NOT NULL";
									break;

								case self::OP_NOTCONTAINS:
								case self::OP_NOT:
									$joins[] = array(
										'custom_data_ticket',
										"LEFT JOIN AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND custom_data_ticket_$join_id.field_id IN ($choices_in)"
									);
									$wheres[] = "custom_data_ticket_$join_id.id IS NULL";
									break;
							}
							break;
					}
					break; // end break TERM_TICKET_FIELD

				case self::TERM_USER_WAITING:
					$this->enableArchiveSearch();
					$this->affected_fields[] = 'ticket.date_user_waiting';

					$choice = $this->normalizeWaitingTime($choice);

					if (is_array($choice) && isset($choice['waiting_time'])) {
						$this->summary[] = 'User waiting time is ' . $choice['waiting_time'] . ' ' . $choice['waiting_time_unit'];
						$choice = new \DateTime('-' . \Orb\Util\Dates::getUnitInSeconds($choice['waiting_time'], $choice['waiting_time_unit']) . ' seconds');

						// Waiting time is inversed when supplied in relative format like this.
						// If we want to know 'waiting time is gte 24 hours', then the date from normaliseWaitingTime is the upper limit of what we want.
						// 'waiting time is gte 24 hours' == 'date_user_waiting lte 2012-01-02'
						$op = $this->invertOp($op);
					}

					if ($choice) {
						$wheres[] = $this->_dateMatch("tickets.date_user_waiting", $op, $choice);
					}
					break;

				case self::TERM_AGENT_WAITING:
					$this->affected_fields[] = 'ticket.date_agent_waiting';

					$choice = $this->normalizeWaitingTime($choice);

					if (is_array($choice) && isset($choice['waiting_time'])) {
						$this->summary[] = 'Agent waiting time is ' . $choice['waiting_time'] . ' ' . $choice['waiting_time_unit'];
						$choice = new \DateTime('-' . \Orb\Util\Dates::getUnitInSeconds($choice['waiting_time'], $choice['waiting_time_unit']) . ' seconds');
						$op = $this->invertOp($op);
					}

					if ($choice) {
						$wheres[] = $this->_dateMatch("$tickets_table.date_agent_waiting", $op, $choice);
					}
					break;

				case self::TERM_TOTAL_USER_WAITING:
					$this->affected_fields[] = 'ticket.total_user_waiting';
					$now = time();

					$choice = $this->normalizeWaitingTime($choice);

					// Need the check on waiting_time because it could be date1/date2 instead
					if (is_array($choice) && isset($choice['waiting_time'])) {
						$this->summary[] = 'Total waiting time is ' . $choice['waiting_time'] . ' ' . $choice['waiting_time_unit'];
						$choice = \Orb\Util\Dates::getUnitInSeconds($choice['waiting_time'], $choice['waiting_time_unit']);
					}

					if ($choice && is_array($choice)) {
						$wheres[] = $this->_rangeMatch("(tickets.total_user_waiting + ($now - COALESCE(UNIX_TIMESTAMP(date_user_waiting), $now)))", 'between', $choice);
					} elseif ($choice) {
						$wheres[] = $this->_rangeMatch("(tickets.total_user_waiting + ($now - COALESCE(UNIX_TIMESTAMP(date_user_waiting), $now)))", $op, $choice);
					}
					break;

				case self::TERM_CREATION_SYSTEM:
					$set_status = true;
					$this->summary[] = $tr->phrase('agent.general.x_is_y', array(
						'field' => $tr->phrase('agent.tickets.creation_system'),
						'value' => $tr->phrase('agent.tickets.creation_system_' . str_replace('.', '_', $choice))
					));
					$wheres[] = $this->_stringMatch("$tickets_table.creation_system", $op, $choice, true, true);
					break;

				case self::TERM_GATEWAY_ADDRESS:
					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.tickets.sent_to_gateway_address'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:EmailGatewayAddress')->getOptions((array)$choice);
						return $titles;
					});

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_GATEWAY_ADDRESS;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.email_gateway_address_id", $op, $choice, true);

					break;

				case self::TERM_RECEIVING_GATEWAY:
					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.tickets.receiving_gateway'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:EmailGateway')->getGatewayNames((array)$choice);
						return $titles;
					});

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_RECEIVING_GATEWAY;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.email_gateway_id", $op, $choice, true);
					break;

				case 'escalation_eliminator':
					/** @var $trigger \Application\DeskPRO\Entity\TicketTrigger */
					$trigger = $choice;
					$field = $trigger->getTicketTimeField();
					if (!$field) {
						break;
					}

					$joins[] = array(
						'ticket_trigger_logs',
						"LEFT JOIN ticket_trigger_logs AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.trigger_id = {$trigger->id} AND $join_name.date_criteria = tickets.$field)"
					);

					$wheres[] = "$join_name.id IS NULL";
					break;

                case 'time_created':
                case 'time_last_user_reply':
                    switch($op) {
                        case 'before':
                            $operator = '<';
                        case 'after':
                            $operator = '>';
                            break;
                        default: $operator = '=';
                    }

                    foreach($choice as $k => $v) {
                        $choice[$k] = preg_replace('[^0-9]', '', $choice[$k]);
                    }

                    $column = str_replace('time', 'date', $term);
                    $wheres[] = "TIME($term) $operator '{$choice['hour1']}:{$choice['minute1']}:00'";
                    break;

                case 'day_created':
                    switch($op) {
                        case 'before':
                            $operator = 'IN';
                        case 'after':
                            $operator = 'NOT IN';
                            break;
                    }

                    foreach($choice as $k => $v) {
                        $choice[$k] = "'".preg_replace('[^A-Za-z]', '', $choice[$k])."'";
                    }

                    $column = str_replace('time', 'date', $term);

                    $wheres[] = "DATE_FORMAT($column, '%W') $op (".implode(',',$choices).')';
                    break;

				default:
					throw new \InvalidArgumentException("Unknown term: $term");
					break;
			}
		}

		if (!$set_status) {
			$wheres[] = $this->_choiceMatch("$tickets_table.status", self::OP_NOT, 'hidden');
		}

		$this->sql_parts = array(
			'joins' => $joins,
			'wheres' => $wheres
		);

		return $this->sql_parts;
	}

	public function normalizeWaitingTime($choice)
	{
		if (is_array($choice)) {
			$choice = Arrays::removeFalsey($choice);
			if (!$choice) {
				return 0;
			}
			if (isset($choice['date1']) || isset($choice['date2'])) {
				return $choice;
			} elseif (empty($choice['waiting_time']) || empty($choice['waiting_time_unit'])) {
				return 0;
			}
		}

		if (!$choice) {
			return 0;
		}

		return $choice;
	}


	/**
	 * Check a specific ticket against these terms to see if it matches.
	 *
	 * @param Ticket $ticket
	 * @return bool
	 */
	public function doesTicketMatch(Entity\Ticket $ticket, $context = null, &$failed_term = null)
	{
		$ignore_terms = array();

		foreach ($this->terms as $term => $info) {
			list($op, $choice) = $info;

			if ($op == 'ignore' || isset($ignore_terms[$term])) {
				$ignore_terms[$term] = 1;
				continue;
			}

			$failed_term = $term;

			switch ($term) {
				case self::TERM_STATUS:
					if (!$this->_testChoiceMatch($ticket['status_code'], $op, $choice)) {
						return false;
					}
					break;
				case self::TERM_DEPARTMENT:
					if (count($choice) == 1) $choice = Arrays::getFirstItem($choice);
					$choice = App::getDataService('Department')->getIdsInTree($choice, true);

					if (!$this->_testChoiceMatch($ticket['department_id'], $op, $choice)) return false;
					break;
				case self::TERM_CATEGORY:
					if (!$this->_testChoiceMatch($ticket['category_id'], $op, $choice)) return false;
					break;
				case self::TERM_PRODUCT:
					if (!$this->_testChoiceMatch($ticket['product_id'], $op, $choice)) return false;
					break;
				case self::TERM_PRIORITY:
					if (!$this->_testChoiceMatch($ticket['priority_id'], $op, $choice)) return false;
					break;
				case self::TERM_ORGANIZATION:
					if (!$this->_testChoiceMatch($ticket['organization_id'], $op, $choice)) return false;
					break;
				case self::TERM_LANGUAGE:
					if (!$this->_testChoiceMatch($ticket['language_id'], $op, $choice)) return false;
					break;
				case self::TERM_HOLD:
					if (!$this->_testChoiceMatch((int)$ticket['is_hold'], $op, $choice)) return false;
					break;
				case self::TERM_AGENT:
					$info = $this->_normalizeAgentChoice($choice);

					$unassigned = $info['unassigned'];
					$agent_ids = $info['agent_ids'];
					$not_id = $info['not_id'];

					if ($unassigned) {
						if ($ticket['agent_id']) return false;
					} else {
						if ($agent_ids) {

							if (!$this->_testChoiceMatch($ticket['agent_id'], $op, $agent_ids)) {
								return false;
							}
						}

						if ($not_id) {
							if ($ticket['agent_id'] == $not_id) return false;
						}
					}

					break;

				case self::TERM_AGENT_TEAM:
					$info = $this->_normalizeAgentTeamChoice($choice);
					$no_team = $info['no_team'];
					$team_ids = $info['team_ids'];
					$not_ids = $info['not_ids'];

					if ($no_team) {
						if ($ticket['agent_team_id']) return false;
					} else {
						if ($team_ids) {
							if (!$this->_testChoiceMatch($ticket['agent_team_id'], $op, $team_ids)) return false;
						}

						if ($not_ids) {
							if (!$this->_testChoiceMatch($ticket['agent_team_id'], 'not', $not_ids)) return false;
						}
					}

					break;

				case self::TERM_USER_WAITING:
					if (!$ticket->date_user_waiting) {
						return false;
					}

					$choice = $this->normalizeWaitingTime($choice);
					if (is_array($choice) && isset($choice['waiting_time'])) {
						$time = time() - $ticket->date_user_waiting->getTimestamp();
						$secs = \Orb\Util\Dates::getUnitInSeconds($choice['waiting_time'], $choice['waiting_time_unit']);

						if (!$this->_testRangeMatch($time, $op, $secs)) {
							return false;
						}
					} else {
						return false;
					}

					break;

				case self::TERM_PARTICIPANT:

					$info = $this->_normalizeAgentChoice($choice);
					$agent_ids = $info['agent_ids'];

					if ($agent_ids) {
						$participant_ids = array();

						if ($context == 'new_match') {
							foreach ($ticket->getOriginalParticipantIds() as $part) {
								$participant_ids[] = $part;
							}
						} else {
							foreach ($ticket->getParticipantPeopleIds() as $part) {
								$participant_ids[] = $part;
							}
						}

						$any = false;
						foreach ($participant_ids as $pid) {
							if ($this->_testChoiceMatch($pid, $op, $agent_ids, true)) {
								$any = true;
								break;
							}
						}

						if (!$any) {
							return false;
						}
					}
					break;

				case self::TERM_SUBJECT:
					$choice = (array)$choice;
					$choice = array_pop($choice);

					switch ($op) {
						case self::OP_IS:
							if ($ticket['subject'] != $choice) return false;
							break;
						case self::OP_NOT:
							if ($ticket['subject'] == $choice) return false;
							break;
						case self::OP_CONTAINS:
							if (strpos(strtolower($ticket['subject']), strtolower($choice)) === false) return false;
							break;
						case self::OP_NOTCONTAINS:
							if (strpos(strtolower($ticket['subject']), strtolower($choice)) !== false) return false;
							break;
					}
					break;

				case self::TERM_CREATION_SYSTEM:
					if (!$this->_testStringMatch($ticket['creation_system'], $op, $choice, true, true)) return false;
					break;

				case self::TERM_GATEWAY_ADDRESS:
					if (!$this->_testChoiceMatch($ticket['email_gateway_address_id'], $op, $choice. true)) return false;
					break;

				case self::TERM_RECEIVING_GATEWAY:
					if (!$this->_testChoiceMatch($ticket['email_gateway_id'], $op, $choice. true)) return false;
					break;

				case self::TERM_DATE_CLOSED:
					if ($ticket['status'] != Ticket::STATUS_CLOSED) return false;
					if (!$this->_testDateMatch($ticket['date_closed'], $op, $choice)) return false;
					break;

				case self::TERM_DATE_RESOLVED:
					if (!$ticket['date_resolved']) return false;
					if (!$this->_testDateMatch($ticket['date_resolved'], $op, $choice)) return false;
					break;

				case self::TERM_DATE_LAST_AGENT_REPLY:
					if (!$ticket['date_last_agent_reply']) return false;
					if (!$this->_testDateMatch($ticket['date_last_agent_reply'], $op, $choice)) return false;
					break;

				case self::TERM_DATE_LAST_USER_REPLY:
					if (!$ticket['date_last_user_reply']) return false;
					if (!$this->_testDateMatch($ticket['date_last_user_reply'], $op, $choice)) return false;
					break;
                case 'time_created':
                case 'time_last_user_reply':
                    $field = str_replace('time', 'date', $term);
                    $time = clone $ticket[$field];
                    $time->setTime($choice['hour1'], $choice['minute1']);

                    switch($op) {
                        case 'before':
                            return $ticket[$field] < $time;
                        case 'after':
                            return $ticket[$field] > $time;
                    }

                    break;
                case 'day_created':
                case 'day_last_user_reply':
                    $field = str_replace('time', 'date', $term);
                    $weekday = $ticket[$field]->format('l');
                    $exists = in_array($weekday, $choice['days']);
                    switch($op) {
                        case 'is':
                            return $exists;
                        case 'not':
                            return !$exists;
                    }
                    break;
			}
		}

		if ($this->person_search) {
			if (!$this->person_search->doesPersontMatch($ticket->person)) {
				return false;
			}
		}

		$failed_term = null;

		return true;
	}


	public static function getTableField($term_id)
	{
		switch ($term_id) {
			case self::TERM_DEPARTMENT: return 'department_id';
			case self::TERM_AGENT: return 'agent_id';
			case self::TERM_AGENT_TEAM: return 'agent_team_id';
			case self::TERM_URGENCY: return 'urgency';
			case self::TERM_CATEGORY: return 'category_id';
			case self::TERM_PRIORITY: return 'priority_id';
			case self::TERM_PRODUCT: return 'product_id';
			case self::TERM_WORKFLOW: return 'workflow_id';
			case self::TERM_LANGUAGE: return 'language_id';
			case self::TERM_ORGANIZATION: return 'organization_id';
			case self::TERM_USER_WAITING: return 'date_user_waiting';
			case self::TERM_TOTAL_USER_WAITING: return 'total_user_waiting';
			case self::TERM_DATE_CREATED: return 'date_created';
			default: throw new \InvalidArgumentException("Invalid field: $term_id");
		}
	}

	/**
	 * @param string $join
	 */
	public function addRawJoin($join)
	{
		$this->add_raw_joins[] = $join;
	}

	/**
	 * @param string $where
	 */
	public function addRawWhere($where)
	{
		$this->add_raw_wheres[] = $where;
	}

	/**
	 * Try to determine whether or not order by/group can be applied to results.
	 *
	 * This is used for a UI enhancement, and getting perfect accuracy is non-trivial.
	 * Additional checks may need to be added to enable this elsewhere.
	 *
	 * In case of any doubt this should return true as it is better to show the options that are of no effect in cases
	 * than to not show it when it is needed.
	 *
	 * @return bool True if urgency options should be applied.
	 */
	public function needsUrgency()
	{
		$terms = $this->getTerms();

		if(!isset($terms['status'])) {
			return true;
		}

		list($op, $data) = $terms['status'];

		if(isset($data['status'])) {
			$status = $data['status'];
		}

		if(isset($data['options']) && isset($data['options']['status'])) {
			$status = $data['options']['status'];
		}

		if(isset($status) && $op == 'is' && $status != 'awaiting_agent') {
			return false;
		}

		return true;
	}
}
