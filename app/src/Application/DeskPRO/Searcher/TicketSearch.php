<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Tickets\TicketTerms;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Orb\Util\Util;

class TicketSearch extends SearcherAbstract
{
	const TERM_ID                        = 'id';
	const TERM_REF                       = 'ref';
	const TERM_PERSON_ID                 = 'person_id';
	const TERM_DEPARTMENT                = 'department';
	const TERM_CATEGORY                  = 'category';
	const TERM_PRODUCT                   = 'product';
	const TERM_AGENT                     = 'agent';
	const TERM_AGENT_TEAM                = 'agent_team';
	const TERM_STATUS                    = 'status';
	const TERM_HIDDEN_STATUS             = 'hidden_status';
	const TERM_EMAIL_ACCOUNT             = 'email_account';
	const TERM_WORKFLOW                  = 'workflow';
	const TERM_PRIORITY                  = 'priority';
	const TERM_SUBJECT                   = 'subject';
	const TERM_SUBJECT_ADV               = 'subject_adv';
	const TERM_MESSAGE                   = 'ticket_message';
	const TERM_MESSAGE_ADV               = 'ticket_message_adv';
	const TERM_ORGANIZATION              = 'organization';
	const TERM_LANGUAGE                  = 'language';
	const TERM_PARTICIPANT               = 'participant';
	const TERM_PERSON                    = 'person';
	const TERM_LABEL                     = 'label';
	const TERM_TICKET_FIELD              = 'ticket_field';
	const TERM_DATE_CREATED              = 'date_created';
	const TERM_DATE_RESOLVED             = 'date_resolved';
	const TERM_DATE_ARCHIVED               = 'date_archived';
	const TERM_DATE_STATUS               = 'date_status';
	const TERM_DATE_LAST_USER_REPLY      = 'date_last_user_reply';
	const TERM_DATE_LAST_AGENT_REPLY     = 'date_last_agent_reply';
	const TERM_DATE_LAST_REPLY           = 'date_last_reply';
	const TERM_URGENCY                   = 'urgency';
	const TERM_USER_WAITING              = 'user_waiting';
	const TERM_TOTAL_USER_WAITING        = 'total_user_waiting';
	const TERM_AGENT_WAITING             = 'agent_waiting';
	const TERM_ARCHIVE_SEARCH            = 'archive_search';
	const TERM_DELETED                   = 'deleted';
	const TERM_CREATION_SYSTEM           = 'creation_system';
	const TERM_HOLD                      = 'is_hold';
	const TERM_FLAGGED                   = 'flagged';
	const TERM_TEXT                      = 'text';
	const TERM_SENT_TO_ADDRESS           = 'sent_to_address';
	const TERM_DAY_CREATED               = 'day_created';
	const TERM_FEEDBACK_RATING           = 'feedback_rating';
	const TERM_SLA                       = 'sla';
	const TERM_SLA_STATUS                = 'sla_status';
	const TERM_SLA_COMPLETED             = 'sla_completed';
	const TERM_IP_ADDRESS                = 'ip_address';

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
	 * @var OrganizationSearch
	 */
	protected $org_search = null;

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
    protected $limit = '10000';

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

	/**
	 * @var null|string
	 */
	public $_last_sql = null;

	/**
	 * Set a set of person search terms.
	 *
	 * @param PersonSearch $person_search
	 */
	public function setPersonSearch(PersonSearch $person_search)
	{
		$person_search->setMode(PersonSearch::MODE_ANY);
		$this->person_search = $person_search;
		$this->person_search->setPerson($this->getPersonContext());
	}


	/**
	 * @param Entity\Person $person
	 */
	public function setPersonContext(Entity\Person $person)
	{
		$this->person = $person;
		if ($this->person_search) {
			$this->person_search->setPerson($person);
		}
	}


	/**
	 * Set a set of org search terms.
	 *
	 * @param OrganizationSearch $org_search
	 */
	public function setOrganizationSearch(OrganizationSearch $org_search)
	{
		$this->org_search = $org_search;
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
        $this->limit = (int) $limit;
    }


	/**
	 * Add a new term.
	 *
	 * @param  $term
	 * @param  $op
	 * @param  $data
	 */
	public function addTerm($term, $op, $data)
	{
		parent::addTerm($term, $op, $data);

		if ($this->isArchiveTerm($term, $op, $data)) {
			$this->is_archive = true;
		}
	}


	/**
	 * Add a new term.
	 *
	 * @param  $term
	 * @param  $op
	 * @param  $data
	 */
	public function addAnyTerm($term, $op, $data)
	{
		parent::addAnyTerm($term, $op, $data);

		if ($this->isArchiveTerm($term, $op, $data)) {
			$this->is_archive = true;
		}
	}


	/**
	 * @param $term
	 * @param $data
	 * @return bool
	 */
	public function isArchiveTerm($term, $op, $data)
	{
		if (!$this->is_archive && $term == self::TERM_STATUS) {
			if (is_array($data) AND count($data) == 1) {
				$data = Arrays::getFirstItem($data);
			}
			if (!is_array($data)) {
				$data = array($data);
			}

			foreach ($data as $s) {
				if ($s == 'closed' || strpos($s, 'hidden') === 0) {
					return true;
				}
			}
		}
		if (!$this->is_archive && $term == self::TERM_HIDDEN_STATUS) {
			return true;
		}
		if (!$this->is_archive && $term == self::TERM_DELETED) {
			return true;
		}

		return false;
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
			$person_summary = $this->person_search->getSummary();
			if ($person_summary) {
				$summary = array_merge($summary, $person_summary);
			}
		}
		if ($this->org_search) {
			$org_summary = $this->org_search->getSummary();
			if ($org_summary) {
				$summary = array_merge($summary, $org_summary);
			}
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

		$db = App::getDbRead('search.filter.tickets', array('query' => $sql));

		try {
			$ticket_ids = $db->fetchAllCol($sql);
		} catch (\PDOException $e) {
			$ticket_ids = array();
			KernelErrorHandler::logException($e, true);

			if (defined('DP_DEBUG') && DP_DEBUG) {
				throw $e;
			}
		}

		$this->getLogger()->logDebug("-- Time: " . sprintf("%.5f", microtime(true) - $time));
		$this->getLogger()->logDebug("-- Count: " . count($ticket_ids));
		$this->getLogger()->logDebug("-- IDs: " . implode(', ', $ticket_ids));

		return $ticket_ids;
	}


	/**
	 * Run the search and get the count
	 */
	public function getCount()
	{
		$ticket_parts = $this->getSqlParts();
		$user_parts   = null;
		$org_parts    = null;
		if ($this->person_search) {
			$user_parts = $this->person_search->getSqlParts();
		}
		if ($this->org_search) {
			$org_parts = $this->org_search->getSqlParts();
		}

		if ($this->isArchiveSearch() || !App::getSetting('core_tickets.use_archive')) {
			$table = 'tickets';
		} else {
			$table = 'tickets_search_active';
		}

		$sql = "SELECT COUNT(tickets.id) AS count FROM $table AS tickets ";
		$sql2 = "SELECT COUNT(part_perm.id) AS count FROM tickets_participants AS part_perm LEFT JOIN $table AS tickets ON (tickets.id = part_perm.ticket_id) ";

		#------------------------------
		# Standard for permissions
		#------------------------------

		$with_part_union = false;

		if ($this->person AND $this->person['is_agent']) {

			$assigned_perm_part = "tickets.agent_id = {$this->person['id']}";
			if ($this->person->getAgentTeamIds()) {
				$assigned_perm_part = "($assigned_perm_part OR tickets.agent_team_id IN (" . implode(',', $this->person->getAgentTeamIds()) . "))";
			}

			$where_perm = array();

			if ($this->person->getDisallowedDepartments()) {
				$where_perm[] = "(tickets.department_id NOT IN (" . implode(',', $this->person->getDisallowedDepartments()) . ") OR tickets.department_id IS NULL OR $assigned_perm_part)";
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
				if ($this->person->hasPerm('agent_tickets.view_unassigned')) {
					$part[] = 'tickets.agent_id IS NULL';
				}

				$where_perm[] = '(' . implode(' OR ', $part) . ')';
			}

			if ($where_perm) {
				$where_perm = '(' . implode(' AND ', $where_perm) . ')';
			} else {
				$where_perm = '';
			}
			$with_part_union = true;

		} else {
			$where_perm = '';
		}

		if ($where_perm) {
			$where_perm .= ' AND ';
		}


		#------------------------------
		# Add joins
		#------------------------------

		$sql_joins = '';

		foreach ($ticket_parts['joins'] as $j) {
			if (is_array($j)) {
				$sql_joins .= $j[1] . " ";
			} else {
				$sql_joins .= "LEFT JOIN $j ON $j.ticket_id = tickets.id ";
			}
		}

		if ($user_parts) {
			$sql_joins .= "LEFT JOIN people ON (people.id = tickets.person_id) ";
		}
		if ($org_parts) {
			$sql_joins .= "LEFT JOIN organizations ON (organizations.id = tickets.organization_id) ";
		}

		if ($user_parts AND $user_parts['joins']) {
			foreach ($user_parts['joins'] as $j) {
				if (is_array($j)) {
					$sql_joins .= $j[1] . " ";
				} else {
					$sql_joins .= "LEFT JOIN $j ON $j.person_id = people.id ";
				}
			}
		}

		if ($org_parts AND $org_parts['joins']) {
			foreach ($org_parts['joins'] as $j) {
				if (is_array($j)) {
					$sql_joins .= $j[1] . " ";
				} else {
					$sql_joins .= "LEFT JOIN $j ON $j.organization_id = organizations.id ";
				}
			}
		}

		if ($this->add_raw_joins) {
			$sql_joins .= implode(' ', $this->add_raw_joins);
		}

		#------------------------------
		# Add wheres
		#------------------------------

		$where = '1';

		if (!empty($ticket_parts['wheres'])) {
			$where .= " AND " . implode(" AND ", $ticket_parts['wheres']);
		}
		if (!empty($ticket_parts['wheres_any'])) {
			$where .= " AND (" . implode(" OR ", $ticket_parts['wheres_any']) . ")";
		}
		if (!empty($user_parts['wheres'])) {
			$where .= " AND " . implode(" AND ", $user_parts['wheres']);
		}
		if (!empty($org_parts['wheres'])) {
			$where .= " AND " . implode(" AND ", $org_parts['wheres']);
		}

		if ($this->add_raw_wheres) {
			$where .= " AND " . implode(' AND ', $this->add_raw_wheres);
		}

		if ($this->is_filter_search && !$this->is_archive) {
			$where .= " AND tickets.status NOT IN ('closed', 'hidden') ";
		}

		$sql .= " $sql_joins ";
		$sql2 .= " $sql_joins ";

		$sql .= " WHERE $where_perm $where";
		$sql2 .= " WHERE $where ";
		if ($this->person) {
			$sql2 .= " AND part_perm.person_id = {$this->person->getId()} ";
		}

		$sql .= " ";
		$sql2 .= " ";

		if ($with_part_union) {
			$count_sql = "
				SELECT SUM(count)
				FROM (
					$sql
					UNION
					$sql2
				) a
			";
		} else {
			$count_sql = $sql;
		}

		$this->getLogger()->logDebug("Search Count Query: " . $count_sql);
		$time = microtime(true);

		$db = App::getDbRead('search.filter.tickets', array('query' => $count_sql));

		try {
			$result = $db->fetchColumn($count_sql);
		} catch (\Doctrine\DBAL\DBALException $e) {
			$result = 0;
			KernelErrorHandler::logException($e, true);

			if (defined('DP_DEBUG') && DP_DEBUG) {
				throw $e;
			}
		}

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
		$user_parts   = null;
		$org_parts    = null;
		if ($this->person_search) {
			$user_parts = $this->person_search->getSqlParts();
		}
		if ($this->org_search) {
			$org_parts = $this->org_search->getSqlParts();
		}

		$order_by = $this->getOrderByPart();

		if ($this->isArchiveSearch() || !App::getSetting('core_tickets.use_archive')) {
			$table = 'tickets';
		} else {
			$table = 'tickets_search_active';
		}

		$select = '';
		if ($this->add_raw_selects) {
			$select = ', ' . implode(', ', $this->add_raw_selects);
		}
		$sql = "SELECT tickets.id $select FROM $table AS tickets ";
		$sql2 = "SELECT part_perm.ticket_id AS id $select FROM tickets_participants AS part_perm LEFT JOIN $table AS tickets ON (tickets.id = part_perm.ticket_id) ";

		#------------------------------
		# Standard for permissions
		#------------------------------

		$with_part_union = false;

		if ($this->person AND $this->person['is_agent']) {

			$assigned_perm_part = "tickets.agent_id = {$this->person['id']}";
			if ($this->person->getAgentTeamIds()) {
				$assigned_perm_part = "($assigned_perm_part OR tickets.agent_team_id IN (" . implode(',', $this->person->getAgentTeamIds()) . "))";
			}

			$where_perm = array();

			if ($this->person->getDisallowedDepartments()) {
				$where_perm[] = "(tickets.department_id NOT IN (" . implode(',', $this->person->getDisallowedDepartments()) . ") OR tickets.department_id IS NULL OR $assigned_perm_part)";
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
				if ($this->person->hasPerm('agent_tickets.view_unassigned')) {
					$part[] = 'tickets.agent_id IS NULL';
				}

				$where_perm[] = '(' . implode(' OR ', $part) . ')';
			}

			if ($where_perm) {
				$where_perm = '(' . implode(' AND ', $where_perm) . ')';
			} else {
				$where_perm = '';
			}
			$with_part_union = true;

		} else {
			$where_perm = '';
		}

		if ($where_perm) {
			$where_perm .= ' AND ';
		}

		#------------------------------
		# Add joins
		#------------------------------

		$sql_joins = '';

		foreach ($ticket_parts['joins'] as $j) {
			if (is_array($j)) {
				$sql_joins .= $j[1] . " ";
			} else {
				$sql_joins .= "LEFT JOIN $j ON $j.ticket_id = tickets.id ";
			}
		}

		if ($user_parts) {
			$sql_joins .= "LEFT JOIN people ON (people.id = tickets.person_id) ";
		}
		if ($org_parts) {
			$sql_joins .= "LEFT JOIN organizations ON (organizations.id = tickets.organization_id) ";
		}

		if ($user_parts AND $user_parts['joins']) {
			foreach ($user_parts['joins'] as $j) {
				if (is_array($j)) {
					$sql_joins .= $j[1] . " ";
				} else {
					$sql_joins .= "LEFT JOIN $j ON $j.person_id = people.id ";
				}
			}
		}

		if ($org_parts AND $org_parts['joins']) {
			foreach ($org_parts['joins'] as $j) {
				if (is_array($j)) {
					$sql_joins .= $j[1] . " ";
				} else {
					$sql_joins .= "LEFT JOIN $j ON $j.organization_id = organizations.id ";
				}
			}
		}

		if (is_array($order_by)) {
			$order_join = $order_by[0];
			$order_by = $order_by[1];

			$sql_joins .= " $order_join ";
		}

		if ($this->add_raw_joins) {
			$sql_joins .= implode(' ', $this->add_raw_joins);
		}

		#------------------------------
		# Add wheres
		#------------------------------

		$where = '1';

		if (!empty($ticket_parts['wheres'])) {
			$where .= " AND " . implode(" AND ", $ticket_parts['wheres']);
		}
		if (!empty($ticket_parts['wheres_any'])) {
			$where .= " AND (" . implode(" OR ", $ticket_parts['wheres_any']) . ")";
		}
		if (!empty($user_parts['wheres'])) {
			$where .= " AND " . implode(" AND ", $user_parts['wheres']);
		}
		if (!empty($org_parts['wheres'])) {
			$where .= " AND " . implode(" AND ", $org_parts['wheres']);
		}

		if ($this->add_raw_wheres) {
			$where .= " AND " . implode(' AND ', $this->add_raw_wheres);
		}

		if ($this->is_filter_search && !$this->is_archive) {
			$where .= " AND tickets.status NOT IN ('closed', 'hidden') ";
		}

		$sql .= " $sql_joins WHERE $where_perm $where";
		$sql2 .= " $sql_joins WHERE $where";

		if ($this->person) {
			$sql2 .= " AND part_perm.person_id = {$this->person->getId()} ";
		}

		$limit_sql = '';
		if ($pageinfo) {
			// A null limit means no limit :o
			if ($pageinfo['limit'] !== null) {
				$limit_sql = " LIMIT {$pageinfo['offset']}, {$pageinfo['limit']} ";
			}
		} else {
			if($this->limit) {
				$limit_sql = ' LIMIT '.$this->limit;
			}
		}

		$sql .= " GROUP BY tickets.id $order_by $limit_sql";
		$sql2 .= " GROUP BY part_perm.id $order_by $limit_sql ";

		if ($with_part_union) {
			$select_query = "
				($sql)
				UNION
				($sql2)
				$order_by
				$limit_sql
			";
		} else {
			$select_query = $sql;
		}

		$this->_last_sql = $select_query;

		return $select_query;
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
					$this->add_raw_selects[] = "IF(tickets.status = 'awaiting_agent', tickets.urgency, IF(tickets.status = 'awaiting_user', 1, 0)) AS status_order";
				} else {
					$this->add_raw_selects[] = "tickets.urgency AS status_order";
				}

				$order_by = "ORDER BY status_order $dir, id $r_dir";
				$this->order_summary = $tr->phrase('agent.general.urgency');
				break;

			case 'ticket.status':
				$this->add_raw_selects[] = "
					CASE WHEN tickets.status =  'awaiting_agent' THEN 1
					WHEN tickets.status =  'awaiting_user' THEN 2
					WHEN tickets.status =  'resolved' THEN 3
					WHEN tickets.status =  'closed' THEN 4
					ELSE 3
					END AS status_order
				";

				$order_by = "ORDER BY status_order $dir, tickets.urgency DESC";
				break;

			case 'ticket.date_created':
				$order_by = "ORDER BY id $dir";
				$this->order_summary = $tr->phrase('agent.general.date_created');
				break;

			case 'ticket.priority':
				$pris = App::getEntityRepository('DeskPRO:TicketPriority')->getIdsInOrder();
				if ($pris) {
					$this->add_raw_selects[] = "FIELD(tickets.priority_id, " . implode(',', $pris) . ") AS status_order";
					$order_by = "ORDER BY status_order $dir, id $r_dir";
				} else {
					$order_by = "ORDER BY id $dir";
				}
				$this->order_summary = $tr->phrase('agent.general.priority');
				break;

			case 'ticket.sla_severity':
				$this->add_raw_selects[] = "MAX(FIELD(sort_table.sla_status, 'ok', 'warning', 'fail')) AS status_order";
				$this->add_raw_selects[] = "IF(MAX(FIELD(sort_table.sla_status, 'ok', 'warning', 'fail')) <= 1, MIN(sort_table.warn_date), MIN(sort_table.fail_date)) AS status_order2";
				$this->order_summary = 'SLA Severity';
				$order_by = array(
					"INNER JOIN ticket_slas AS sort_table ON (sort_table.ticket_id = tickets.id)",
					"ORDER BY status_order $dir, status_order2 $dir"
				);
				break;

			case 'ticket.date_resolved':
				$this->add_raw_selects[] = "tickets.date_resolved AS status_order";
				$this->order_summary = $tr->phrase('agent.general.date_resolved');
				$order_by = "ORDER BY status_order $dir, id DESC";
				break;

			case 'ticket.date_archived':
				$this->add_raw_selects[] = "tickets.date_archived AS status_order";
				$this->order_summary = $tr->phrase('agent.general.date_opened');
				$order_by = "ORDER BY status_order $dir, id DESC";
				break;

			case 'ticket.last_activity':
				$this->add_raw_selects[] = "tickets.date_last_user_reply AS status_order";
				$this->order_summary = $tr->phrase('agent.general.date_of_last_user_reply');
				$order_by = "ORDER BY status_order $dir, id DESC";
				break;

            case 'ticket.total_user_waiting':
				$this->add_raw_selects[] = "tickets.total_user_waiting AS status_order";
                $this->order_summary = $tr->phrase('agent.general.total_time_waiting');
                $order_by = "ORDER BY status_order $dir, id DESC";
                break;

            case 'ticket.date_user_waiting':
				$this->add_raw_selects[] = "tickets.date_user_waiting AS status_order";
                $this->order_summary = $tr->phrase('agent.general.time_waiting');
                $order_by = "ORDER BY status_order $dir, id DESC";
                break;

			case 'ticket.organization':
				$this->add_raw_selects[] = "sort_table.name AS status_order";
				$this->order_summary = $tr->phrase('agent.general.organization_name');
				$order_by = array(
					"INNER JOIN organizations AS sort_table ON (sort_table.id = tickets.organization_id)",
					"ORDER BY status_order $dir, id DESC"
				);
				break;

			case 'ticket.date_last_user_reply':
				$this->add_raw_selects[] = "tickets.date_last_user_reply AS status_order";
				$this->order_summary = 'Date of Last User Reply';
				$order_by = "ORDER BY status_order $dir, id DESC";
				break;

			case 'ticket.date_last_agent_reply':
				$this->order_summary = 'Date of Last Agent Reply';
				$this->add_raw_selects[] = "tickets.date_last_agent_reply AS status_order";
				$order_by = "ORDER BY status_order $dir, id DESC";
				break;

			case 'ticket.date_last_reply':
				$this->order_summary = 'Date of Last Reply';
				$this->add_raw_selects[] = "GREATEST(COALESCE(tickets.date_last_agent_reply, '0000-00-00'), COALESCE(tickets.date_last_user_reply, '0000-00-00'), tickets.date_created) AS status_order";
				$order_by = "ORDER BY status_order $dir, id DESC";
				break;

			case 'ticket.ticket_field':
				$field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($term_id);
				if (!$field) break;

				$this->order_summary = $field['title'];

				$search_type = $field->getHandler()->getSearchType();

				switch ($search_type) {
					case 'input':
					case 'value':
						$this->add_raw_selects[] = "sort_table.$search_type AS status_order";
						$order_by = array(
							"INNER JOIN custom_data_ticket AS sort_table ON (sort_table.ticket_id = tickets.id AND sort_table.id = $term_id)",
							"ORDER BY status_order $dir, id DESC"
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

		$tr = App::getTranslator();

		$joins = array();

		$wheres_all = array();
		$wheres_any = array();

		// If we dont set a status, we will automatically
		// exclude 'hidden' tickets
		$set_status = false;

		foreach (array(array('all', $this->terms), array('any', $this->terms_any)) as $term_set) {

			if ($term_set[0] == 'all') {
				$wheres = &$wheres_all;
			} else {
				$wheres = &$wheres_any;
			}

			foreach ($term_set[1] as $info) {

				if (!$info || !is_array($info)) continue;

				$join_id = Util::requestUniqueId();
				$join_name = "j_$join_id";

				list($term, $op, $choice) = $info;

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

				if (!$term) {
					//skip empty terms
					continue;
				}

				switch ($term) {
					case self::TERM_ID:
						$this->enableArchiveSearch();

						$choice = is_array($choice) && isset($choice['ticket_id']) ? $choice['ticket_id'] : $choice;
						if (!is_array($choice)) {
							$choice = array($choice);
						}

						if (!$choice) {
							$choice = array(0);
						}

						if ($op == self::OP_IS) {
							$wheres[] = "$tickets_table.id IN (" . implode(',', $choice) . ")";
						} else {
							$wheres[] = $this->_rangeMatch("$tickets_table.id", $op, $choice, true);
						}
						break;

					case self::TERM_REF:
						$this->enableArchiveSearch();
						$wheres[] = $this->_stringMatch("$tickets_table.ref", $op, $choice, true);
						break;

					case self::TERM_PERSON_ID:
						$choice = is_array($choice) && isset($choice['person_id']) ? $choice['person_id'] : $choice;
						if (!is_array($choice)) {
							$choice = array($choice);
						}

						if ($op == self::OP_IS) {
							$wheres[] = "$tickets_table.person_id IN (" . implode(',', $choice) . ")";
						} else {
							$wheres[] = $this->_rangeMatch("$tickets_table.person_id", $op, $choice, true);
						}
						break;

					case self::TERM_ARCHIVE_SEARCH:
						if ($choice) {
							$this->enableArchiveSearch();
						}
						break;
					case self::TERM_DEPARTMENT:

						$this->affected_fields[] = 'ticket.department_id';

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

					case self::TERM_EMAIL_ACCOUNT:
						$this->enableArchiveSearch();
						$this->affected_fields[] = 'ticket.email_account_id';

						if ($choice && (!is_array($choice) || !in_array('0', $choice))) {
							$choice = (array)$choice;
							$choice = array_unique($choice, \SORT_NUMERIC);
						}

						if (count($choice) == 1) {
							$this->specific_fields[] = self::TERM_EMAIL_ACCOUNT;
						}

						$wheres[] = $this->_choiceMatch("$tickets_table.email_account_id", $op, $choice, true);
						break;

					case self::TERM_DELETED:
						$this->affected_fields[] = 'ticket.status';
						$this->affected_fields[] = 'ticket.hidden_status';

						$set_status = true;
						$wheres[] = $this->_choiceMatch("$tickets_table.status", self::OP_IS, 'hidden');
						$wheres[] = $this->_choiceMatch("$tickets_table.hidden_status", self::OP_IS, 'deleted');

						$this->enableArchiveSearch();

						break;
					case self::TERM_CATEGORY:
						$this->affected_fields[] = 'ticket.category_id';

						if (count($choice) == 1) {
							$this->specific_fields[] = self::TERM_CATEGORY;
						}

						$wheres[] = $this->_choiceMatch("$tickets_table.category_id", $op, $choice, true);
						break;
					case self::TERM_PRODUCT:
						$this->affected_fields[] = 'ticket.product_id';

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
						$wheres[] = $this->_rangeMatch("$tickets_table.urgency", $op, $choice);
						break;
					case self::TERM_DATE_CREATED:
						$wheres[] = $this->_dateMatch("$tickets_table.date_created", $op, $choice);
						break;
					case self::TERM_DATE_STATUS:
						$wheres[] = $this->_dateMatch("$tickets_table.date_status", $op, $choice);
						$this->enableArchiveSearch();
						break;
					case self::TERM_DATE_RESOLVED:
						$this->enableArchiveSearch();
						$this->affected_fields[] = 'ticket.date_resolved';
						$wheres[] = $this->_dateMatch("$tickets_table.date_resolved", $op, $choice);
						$wheres[] = $this->_choiceMatch("$tickets_table.status", 'is', array('resolved'));
						break;
					case self::TERM_DATE_ARCHIVED:
						$this->enableArchiveSearch();
						$this->affected_fields[] = 'ticket.date_archived';
						if (!$this->is_testing) $this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.general.date_archived'), $op, $choice);
						$wheres[] = $this->_dateMatch("$tickets_table.date_archived", $op, $choice);
						$wheres[] = $this->_choiceMatch("$tickets_table.status", 'is', array('closed'));
						break;
					case self::TERM_DATE_LAST_USER_REPLY:
						$this->enableArchiveSearch();
						$this->affected_fields[] = 'ticket.date_last_user_reply';

						$wheres[] = $this->_dateMatch("$tickets_table.date_last_user_reply", $op, $choice);
						break;
					case self::TERM_DATE_LAST_AGENT_REPLY:
						$this->enableArchiveSearch();
						$this->affected_fields[] = 'ticket.date_last_agent_reply';

						$wheres[] = $this->_dateMatch("$tickets_table.date_last_agent_reply", $op, $choice);
						break;
					case self::TERM_DATE_LAST_REPLY:
						$this->enableArchiveSearch();
						$this->affected_fields[] = 'ticket.date_last_reply';

						$wheres[] = $this->_dateMatch("GREATEST(COALESCE($tickets_table.date_last_agent_reply, '0000-00-00'), COALESCE($tickets_table.date_last_user_reply, '0000-00-00'), $tickets_table.date_created)", $op, $choice);
						break;
					case self::TERM_WORKFLOW:
						$this->affected_fields[] = 'ticket.workflow_id';
						if (count($choice) == 1) {
							$this->specific_fields[] = self::TERM_WORKFLOW;
						}

						$wheres[] = $this->_choiceMatch("$tickets_table.workflow_id", $op, $choice, true);
						break;
					case self::TERM_FEEDBACK_RATING:

						$choice = (is_array($choice) && isset($choice['rating'])) ? $choice['rating'] : 'set';

						if ($choice == 'set') {
							if ($op == self::OP_IS) {
								$wheres[] = "$tickets_table.feedback_rating IS NOT NULL";
							} else {
								$wheres[] = "$tickets_table.feedback_rating IS NULL";
							}
						} else {
							$op = $op == self::OP_IS ? '=' : '!=';

							$check = '';
							if ($choice == 'positive') {
								$check .= "$tickets_table.feedback_rating $op 1";
							} elseif ($choice == 'negative') {
								$check .= "$tickets_table.feedback_rating $op -1";
							} else {
								$check .= "$tickets_table.feedback_rating $op 0";
							}

							if ($op == '=') {
								$wheres[] = "($tickets_table.feedback_rating IS NOT NULL AND $check)";
							} else {
								$wheres[] = "($tickets_table.feedback_rating IS NULL OR $check)";
							}
						}

						break;

					case self::TERM_SLA:

						$choice = (array)((is_array($choice) && isset($choice['sla_id'])) ? $choice['sla_id'] : $choice);
						if (!$choice) {
							((is_array($choice) && isset($choice['sla_ids'])) ? $choice['sla_ids'] : $choice);
						}
						if (!$choice) {
							break;
						}

						$this->affected_fields[] = 'ticket.sla_id';

						$choices_in = array();
						foreach ($choice as $c) {
							$choices_in[] = $this->quoteDbValue($c);
						}
						$choices_in = implode(',', $choices_in);

						switch ($op) {
							case self::OP_IS:
							case self::OP_CONTAINS:
								$joins[] = array(
									'ticket_slas',
									"LEFT JOIN ticket_slas AS $join_name ON ($join_name.ticket_id = tickets.id)"
								);
								$wheres[] = "$join_name.sla_id IN ($choices_in)";
								break;

							case self::OP_NOT:
							case self::OP_NOTCONTAINS:
								$joins[] = array(
									'ticket_slas',
									"LEFT JOIN ticket_slas AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.sla_id IN ($choices_in))"
								);
								$wheres[] = "$join_name.ticket_id IS NULL";
								break;
						}
						break;

					case self::TERM_SLA_STATUS:

						if (is_array($choice) && isset($choice['sla_status'])) {
							$statuses = (array)$choice['sla_status'];
							$sla_ids = (array)(isset($choice['sla_id']) ? $choice['sla_id'] : array());
							if (!$sla_ids) {
								(isset($choice['sla_ids']) ? $choice['sla_ids'] : array());
							}
						} else {
							$statuses = (array)$choice;
							$sla_ids = array();
						}
						if (!$statuses && !$sla_ids) {
							break;
						}

						if (!$statuses) {
							$statuses = array('ok', 'warning', 'fail');
							$status_summary = false;
						} else {
							$status_summary = true;
						}

						$this->affected_fields[] = 'ticket.sla_status';
						if ($sla_ids) {
							$this->affected_fields[] = 'ticket.sla_id';
						}

						$statuses_in = array();
						$sla_ids_in = array();

						foreach ($statuses as $c) {
							$statuses_in[] = $this->quoteDbValue($c);
						}
						foreach ($sla_ids as $c) {
							if ($c) {
								$sla_ids_in[] = $this->quoteDbValue($c);
							}
						}
						$statuses_in = implode(',', $statuses_in);
						$sla_ids_in = implode(',', $sla_ids_in);

						switch ($op) {
							case self::OP_IS:
							case self::OP_CONTAINS:
								$joins[] = array(
									'ticket_slas',
									"LEFT JOIN ticket_slas AS $join_name ON ($join_name.ticket_id = tickets.id)"
								);
								$wheres[] = "$join_name.sla_status IN ($statuses_in)"
									. ($sla_ids_in ? " AND $join_name.sla_id IN ($sla_ids_in)" : '');
								break;

							case self::OP_NOT:
							case self::OP_NOTCONTAINS:
								$joins[] = array(
									'ticket_slas',
									"LEFT JOIN ticket_slas AS $join_name ON ($join_name.ticket_id = tickets.id"
										. " AND $join_name.sla_status IN ($statuses_in)"
										. ($sla_ids_in ? " AND $join_name.sla_id IN ($sla_ids_in)" : '') . ")"
								);
								$wheres[] = "$join_name.ticket_id IS NULL";
								break;
						}
						break;

					case self::TERM_SLA_COMPLETED:

						if (is_array($choice) && isset($choice['is_completed'])) {
							$completed = (array)$choice['is_completed'];
							$sla_ids = (array)(isset($choice['sla_id']) ? $choice['sla_id'] : array());
						} else {
							$completed = (array)$choice;
							$sla_ids = array();
						}
						if (!$completed && !$sla_ids) {
							break;
						}

						if (!$completed) {
							$completed = array(1, 0);
							$status_summary = false;
						} else {
							$status_summary = true;
						}

						$this->affected_fields[] = 'ticket.sla_completed';
						if ($sla_ids) {
							$this->affected_fields[] = 'ticket.sla_id';
						}

						$completed_in = array();
						$sla_ids_in = array();

						foreach ($completed as $c) {
							$completed_in[] = $this->quoteDbValue($c);
						}
						foreach ($sla_ids as $c) {
							if ($c) {
								$sla_ids_in[] = $this->quoteDbValue($c);
							}
						}
						$completed_in = implode(',', $completed_in);
						$sla_ids_in = implode(',', $sla_ids_in);

						switch ($op) {
							case self::OP_IS:
							case self::OP_CONTAINS:
								$joins[] = array(
									'ticket_slas',
									"LEFT JOIN ticket_slas AS $join_name ON ($join_name.ticket_id = tickets.id)"
								);
								$wheres[] = "$join_name.is_completed IN ($completed_in)"
									. ($sla_ids_in ? " AND $join_name.sla_id IN ($sla_ids_in)" : '');
								break;

							case self::OP_NOT:
							case self::OP_NOTCONTAINS:
								$joins[] = array(
									'ticket_slas',
									"LEFT JOIN ticket_slas AS $join_name ON ($join_name.ticket_id = tickets.id"
										. "AND $join_name.is_completed IN ($completed_in)"
										. ($sla_ids_in ? " AND $join_name.sla_id IN ($sla_ids_in)" : '') . ")"
								);
								$wheres[] = "$join_name.ticket_id IS NULL";
								break;
						}
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

						$info       = $this->_normalizeAgentChoice($choice);
						$unassigned = $info['unassigned'];
						$agent_ids  = $info['agent_ids'];
						$not_id     = $info['not_id'];

						if ($not_id) {
							$wheres[] = "$tickets_table.agent_id != " . $not_id;
						} else {
							$w = array();

							if ($unassigned) {

								if ($op == self::OP_IS || $op == self::OP_CONTAINS) {
									$w[] = "$tickets_table.agent_id IS NULL";
								} else {
									$w[] = "$tickets_table.agent_id IS NOT NULL";
								}
							}

							if ($agent_ids) {
								if (count($agent_ids) == 1) {
									$this->specific_fields[] = self::TERM_AGENT;
								}

								$w[] = $this->_choiceMatch("$tickets_table.agent_id", $op, $agent_ids, true);
							}

							if (count($w) === 1) {
								$wheres[] = $w[0];
							} elseif ($w) {
								$wheres[] = '((' . implode(') OR (', $w) . '))';
							}
						}
						break;
					case self::TERM_AGENT_TEAM:
						$this->affected_fields[] = 'ticket.agent_team_id';

						$info = $this->_normalizeAgentTeamChoice($choice);
						$team_ids = $info['team_ids'];
						$not_ids = $info['not_ids'];
						$no_team = $info['no_team'];

						if ($not_ids) {
							$wheres[] = $this->_choiceMatch("$tickets_table.agent_team_id", 'not', $team_ids, true);
						} else {
							$w = array();

							if ($no_team ) {
								if ($op == self::OP_IS || $op == self::OP_CONTAINS) {
									$w[] = "$tickets_table.agent_team_id IS NULL";
								} else {
									$w[] = "$tickets_table.agent_team_id IS NOT NULL";
								}
							}

							if ($team_ids) {
								if (count($choice) == 1) {
									$this->specific_fields[] = self::TERM_AGENT_TEAM;
								}

								$w[] = $this->_choiceMatch("$tickets_table.agent_team_id", $op, $team_ids, true);
							}

							if (count($w) === 1) {
								$wheres[] = $w[0];
							} elseif ($w) {
								$wheres[] = '((' . implode(') OR (', $w) . '))';
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
								if ($status == 'hidden' && $tr->hasPhrase('agent.tickets.hidden_status_' . $hstatus)) {
									$choice_str[] = $tr->phrase('agent.tickets.hidden_status_' . $hstatus);
								}
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

						$w = '(';
						if ($show_status) {
							$w .= '(';
							$w .= $this->_choiceMatch("$tickets_table.status", $op, $choice);
							$w .= ')';
						} else {
							$w .= '(';
							$w .= "$tickets_table.status = 'hidden' AND ";
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

						break;
					case self::TERM_ORGANIZATION:
						if (isset($choice['organization_ids']) && is_array($choice['organization_ids'])) {
							$choice = $choice['organization_ids'];
						}
						if (!is_array($choice)) {
							$choice = explode(',', $choice);
						}

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

						$wheres[] = $this->_choiceMatch($field, $op, $choice);
						break;
					case self::TERM_PERSON:
						if (count($choice) == 1) {
							$this->specific_fields[] = self::TERM_PERSON;
						}

						$wheres[] = $this->_choiceMatch("$tickets_table.person_id", $op, $choice, true);
						break;

					case self::TERM_IP_ADDRESS:
						$choice = is_array($choice) ? array_pop($choice) : $choice;
						$choice = preg_replace('#[^0-9\.]#', '', $choice);

						$joins[] = array(
							'tickets_messages',
							"LEFT JOIN tickets_messages AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.ip_address != '')"
						);
						$field = "$join_name.ip_address";

						// If last char is a dot, then do a wildcard suffix search
						if (substr($choice, -1, 1) == '.') {
							$wheres[] = $this->_stringMatch($field, $op, $choice, true, true);
						} else {
							$wheres[] = $this->_stringMatch($field, $op, $choice);
						}

						break;

					case self::TERM_SUBJECT:
						$this->affected_fields[] = 'ticket.subject';
						$field = 'tickets.subject';

						$wheres[] = $this->_stringMatch($field, $op, $choice);
						break;

					case self::TERM_SUBJECT_ADV:
						$this->affected_fields[] = 'ticket.subject';
						$field = 'tickets.subject';
						$string = $choice['query'];
						$type = !empty($choice['type']) ? $choice['type'] : 'phrase';

						$wheres[] = $this->_stringSearch($field, $op, $string, $type);
						break;

					case self::TERM_MESSAGE:
						$this->affected_fields[] = 'ticket.message';

						$string = $choice;

						if (is_array($string)) {
							$string = array_pop($string);
						}

						$type = 'and';

						$this->affected_fields[] = 'ticket.message';
						$joins[] = array(
							'tickets_messages',
							"LEFT JOIN tickets_messages AS $join_name ON ($join_name.ticket_id = tickets.id)"
						);
						$field = "$join_name.message";
						$wheres[] = $this->_stringSearch($field, $op, $string, $type);

						break;

					case self::TERM_MESSAGE_ADV:

						$string = $choice['query'];
						$type = !empty($choice['type']) ? $choice['type'] : 'phrase';

						$this->affected_fields[] = 'ticket.message';
						$joins[] = array(
							'tickets_messages',
							"LEFT JOIN tickets_messages AS $join_name ON ($join_name.ticket_id = tickets.id)"
						);
						$field = "$join_name.message";

						$w = array();
						$w[] = '('.$this->_stringSearch($field, $op, $string, $type).')';

						if (!empty($choice['who'])) {
							$join_name2 = $join_name.'_u';
							$joins[] = array(
								'people',
								"LEFT JOIN people AS $join_name2 ON ($join_name2.id = $join_name.person_id)"
							);

							if ($choice['who'] == 'agent') {
								$w[] = "($join_name2.is_agent = 1)";
							} else {
								$w[] = "($join_name2.is_agent = 0)";
							}
						}

						if (!empty($choice['date_op']) && $choice['date_op']) {
							$w[] = '(' . $this->_dateMatch("$join_name.date_created", $choice['date_op'], $choice['date']) . ')';
						}

						$wheres[] = '(' . implode(' AND ', $w) . ')';
						break;

					case self::TERM_FLAGGED:

						$this->affected_fields[] = 'tickets_flagged';
						$joins[] = 'tickets_flagged';

						$color = $choice;
						if ($color == 'any') {
							$wheres[] = 'tickets_flagged.person_id = '. $this->person->id;
						} else {
							$wheres[] = '(tickets_flagged.person_id = '. $this->person->id . ' AND ' . $this->_stringMatch('tickets_flagged.color', $op, $color) . ')';
						}

						break;

					case self::TERM_LABEL:
						$this->affected_fields[] = 'ticket.labels';
						$this->_normalizeOpAndChoice($op, $choice);

						$choices_in = array();
						if (is_array($choice)) {
							foreach ((array)$choice as $c) {
								$choices_in[] = $this->quoteDbValue($c);
							}
							$choices_in = implode(',', $choices_in);
							if (!$choices_in) $choices_in = '';
						}

						switch ($op) {
							case self::OP_IS:
								$joins[] = array(
									'labels_tickets',
									"LEFT JOIN labels_tickets AS $join_name ON ($join_name.ticket_id = tickets.id)"
								);
								$wheres[] = "$join_name.label = " . $this->quoteDbValue($choice);
								break;
							case self::OP_NOT:
								$joins[] = array(
									'labels_tickets',
									"LEFT JOIN labels_tickets AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.label = ".$this->quoteDbValue($choice).")"
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
									"LEFT JOIN labels_tickets AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.label IN ($choices_in))"
								);
								$wheres[] = "$join_name.ticket_id IS NULL";
								break;
						}
						break;

					case self::TERM_TICKET_FIELD:
						$field_def = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($term_id);
						if (!$field_def) break;

						$this->affected_fields[] = 'ticket.custom_data_ticket_' . $field_def['id'];

						$search_type = $field_def->getHandler()->getSearchType();

						if (is_array($choice) && isset($choice['custom_fields']['field_' . $term_id])) {
							$choice = $choice['custom_fields']['field_' . $term_id];
						}

						if (is_array($choice) && isset($choice['value'])) {
							$choice = $choice['value'];
						}

						switch ($search_type) {
							case 'input':
							case 'value':

								if (is_array($choice)) {
									$choice = array_pop($choice);
								}

								$joins[] = array(
									'custom_data_ticket',
									"LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND custom_data_ticket_$join_id.field_id = $term_id)"
								);

								$field = 'custom_data_ticket_'.$join_id.'.'.$search_type;
								switch ($op) {
									case self::OP_IS:
										if ($choice == 'DP_NO_SELECTION') {
											$wheres[] = "$field IS NULL";
										} else {
											$wheres[] = "$field = " . $this->quoteDbValue($choice);
										}
										break;
									case self::OP_NOT:
										if ($choice == 'DP_NO_SELECTION') {
											$wheres[] = "$field IS NOT NULL";
										} else {
											$w = "$field != " . $this->quoteDbValue($choice);

											if ($choice != "") {
												$w = "($w OR $field IS NULL)";
											}

											$wheres[] = $w;
										}
										break;
									case self::OP_CONTAINS:
									case self::OP_NOTCONTAINS:
										$op = 'LIKE';
										if ($op == self::OP_NOTCONTAINS) $op = 'NOT LIKE';
										$w = "$field $op " . $this->quoteDbValue('%'.$choice.'%');

										if ($op == self::OP_NOTCONTAINS) {
											$w = "($w OR $field IS NULL)";
										}

										$wheres[] = $w;

										break;
								}

								break;

							case 'id':
								$join_id = Util::requestUniqueId();
								$choices_in = array();

								if ($choice != 'DP_NO_SELECTION') {
									$choice = (array)$choice;
									if (isset($choice["field_{$field_def->getId()}"])) {
										$choice = $choice["field_{$field_def->getId()}"];
									}
									if (!is_array($choice)) {
										$choice = array($choice);
									}
									foreach ($choice as $c) {
										$choices_in[] = (int)$c;
									}
									$choices_in = implode(',', $choices_in);

									$choice_str = array();
									foreach ($field_def->children as $child) {
										if (in_array($child['id'], $choice)) {
											$choice_str[] = $child['title'];
										}
									}
									$choice_str = implode(', ', $choice_str);
								}

								$field = 'custom_data_ticket_'.$join_id.'.field_id';
								switch ($op) {
									case self::OP_CONTAINS:
									case self::OP_IS:
										if ($choice == 'DP_NO_SELECTION') {
											$joins[] = array(
												'custom_data_ticket',
												"LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND custom_data_ticket_$join_id.root_field_id = {$field_def->id})"
											);
											$wheres[] = "custom_data_ticket_$join_id.id IS NULL";
										} else {
											$joins[] = array(
												'custom_data_ticket',
												"LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND $field IN ($choices_in))"
											);
											$wheres[] = "custom_data_ticket_$join_id.id IS NOT NULL";
										}
										break;

									case self::OP_NOTCONTAINS:
									case self::OP_NOT:
										if ($choice == 'DP_NO_SELECTION') {
											$joins[] = array(
												'custom_data_ticket',
												"LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND custom_data_ticket_$join_id.root_field_id = {$field_def->id})"
											);
											$wheres[] = "custom_data_ticket_$join_id.id IS NOT NULL";
										} else {
											$joins[] = array(
												'custom_data_ticket',
												"LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND $field IN ($choices_in))"
											);
											$wheres[] = "custom_data_ticket_$join_id.id IS NULL";
										}
										break;
								}
								break;
						}
						break; // end break TERM_TICKET_FIELD

					case 'time_waiting':
					case self::TERM_USER_WAITING:
						$this->enableArchiveSearch();
						$this->affected_fields[] = 'ticket.date_user_waiting';

						$choice = $this->normalizeWaitingTime($choice);

						if (is_array($choice) && isset($choice['waiting_time'])) {
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

						// Need the check on waiting_time because it could be date1/date2 instead
						if (is_array($choice) && isset($choice['waiting_time'])) {
							$choice = \Orb\Util\Dates::getUnitInSeconds($choice['waiting_time'], $choice['waiting_time_unit']);
						}

						$wheres[] = $this->_rangeMatch("(tickets.total_user_waiting + ($now - COALESCE(UNIX_TIMESTAMP(date_user_waiting))))", $op, $choice);
						break;

					case self::TERM_CREATION_SYSTEM:
						$set_status = true;
						break;

					case 'escalation_eliminator':
						/** @var $trigger \Application\DeskPRO\Entity\TicketEscalation */
						if ($choice instanceof Entity\TicketEscalation) {
							$escalation = $choice;
						} else {
							$escalation = $choice['escalation'];
						}
						$field = $escalation->getTicketTimeField();
						if (!$field) {
							break;
						}

						$joins[] = array(
							'ticket_escalation_logs',
							"LEFT JOIN ticket_escalation_logs AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.escalation_id = {$escalation->id} AND $join_name.date_criteria = tickets.$field)"
						);

						$wheres[] = "$join_name.id IS NULL";
						break;

					case 'time_created':
					case 'time_last_user_reply':
						switch($op) {
							case 'before':
								$operator = '<=';
								break;
							case 'after':
								$operator = '>=';
								break;
							default:
								$operator = '=';
						}

						foreach($choice as $k => $v) {
							$choice[$k] = preg_replace('[^0-9]', '', $choice[$k]);
						}

						$column = str_replace('time', 'date', $term);
						$wheres[] = "$column IS NOT NULL AND TIME($column) $operator '{$choice['hour1']}:{$choice['minute1']}:00'";
						break;

					case self::TERM_DAY_CREATED:
						$days = (is_array($choice) && isset($choice['days'])) ? $choice['days'] : $choice;
						if (!$days || !is_array($days)) {
							continue;
						}
						$wheres[] = $this->_choiceMatch("DATE_FORMAT(tickets.date_created, '%w')", $op, $days, true);
						break;

					default:
						$e = new \InvalidArgumentException("Unknown term: $term");
						\DeskPRO\Kernel\KernelErrorHandler::logErrorInfo(\DeskPRO\Kernel\KernelErrorHandler::getExceptionInfo($e));
						break;
				}
			}
		}

		if (!$set_status) {
			$wheres_all[] = $this->_choiceMatch("$tickets_table.status", self::OP_NOT, 'hidden');
		}

		$this->sql_parts = array(
			'joins' => $joins,
			'wheres' => $wheres_all,
			'wheres_any' => $wheres_any,
		);

		return $this->sql_parts;
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

		if ($this->is_filter_search && $ticket->hidden_status) {
			return false;
		}

		foreach ($this->terms as $info) {
			list($term, $op, $choice) = $info;

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
					if (!is_array($choice)) {
						$choice = array($choice);
					}

					foreach ($choice as $id) {
						$choice = array_merge($choice, App::getDataService('Department')->getIdsInTree($id, true));
					}
					$choice = array_unique($choice, \SORT_NUMERIC);

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
				case self::TERM_WORKFLOW:
					if (!$this->_testChoiceMatch($ticket['workflow_id'], $op, $choice)) return false;
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
					if (isset($choice['agent_ids'])) {
						$choice = $choice['agent_ids'];
					}
					if (isset($choice['agent'])) {
						$choice = $choice['agent'];
					}
					$info = $this->_normalizeAgentChoice($choice);

					$unassigned = $info['unassigned'];
					$agent_ids = $info['agent_ids'];
					$not_id = $info['not_id'];

					if ($unassigned) {
						if ($ticket['agent_id'] && $op == self::OP_IS) return false;
						if (!$ticket['agent_id'] && $op != self::OP_IS) return false;
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
					if (isset($choice['team_ids'])) {
						$choice = $choice['team_ids'];
					}
					if (isset($choice['agent_team'])) {
						$choice = $choice['agent_team'];
					}
					$info = $this->_normalizeAgentTeamChoice($choice);
					$no_team = $info['no_team'];
					$team_ids = $info['team_ids'];
					$not_ids = $info['not_ids'];

					if ($no_team) {
						if ($ticket['agent_team_id'] && $op == self::OP_IS) return false;
						if (!$ticket['agent_team_id'] && $op != self::OP_IS) return false;
					} else {
						if ($team_ids) {
							if (!$this->_testChoiceMatch($ticket['agent_team_id'], $op, $team_ids)) return false;
						}

						if ($not_ids) {
							if (!$this->_testChoiceMatch($ticket['agent_team_id'], 'not', $not_ids)) return false;
						}
					}

					break;

				case 'time_waiting':
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
					$choice = (array)$info['agent_ids'];
					if (count($choice) == 1) $choice = array_pop($choice);

					$participant_ids = $ticket->getParticipantPeopleIds();

					if (is_array($choice)) {
						$any = false;
						foreach ($choice as $person_id) {
							$is_in = in_array($person_id, $participant_ids);

							if ($is_in) {
								$any = true;
								if ($op == self::OP_CONTAINS) {
									break;
								} else {
									return false;
								}
							}
						}

						if ($op == self::OP_CONTAINS AND !$any) return false;
					} else {
						if (in_array($choice, $participant_ids)) {
							if ($op == self::OP_NOT) return false;
						} else {
							if ($op == self::OP_IS) return false;
						}
					}
					break;

				case self::TERM_LABEL:

					$choice_labels = array();
					if (!empty($choice['labels'])) {
						foreach ($choice['labels'] as $l) {
							$l = Strings::utf8_strtolower($l);
							$choice_labels[$l] = $l;
						}
					}

					$has = false;
					foreach ($ticket->labels as $l) {
						$l = Strings::utf8_strtolower($l->label);
						if (isset($choice_labels[$l])) {
							$has = true;
							break;
						}
					}

					if ($op == self::OP_IS || $op == self::OP_CONTAINS) {
						if (!$has) {
							return false;
						}
					} else {
						if ($has) {
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


				case self::TERM_SENT_TO_ADDRESS:
					$choice = (array)$choice;
					$choice = array_pop($choice);

					$choice = strtolower($choice);
					$has = $ticket->hasSentToAddress($choice);

					switch ($op) {
						case self::OP_IS:
						case self::OP_CONTAINS:
							if (!$has) return false;
							break;
						case self::OP_NOT:
						case self::OP_NOTCONTAINS:
							if ($has) return false;
							break;
					}
					break;

				case self::TERM_CREATION_SYSTEM:
					if (!$this->_testStringMatch($ticket['creation_system'], $op, $choice, true, true)) return false;
					break;

				case self::TERM_DATE_ARCHIVED:
					if ($ticket['status'] != Ticket::STATUS_CLOSED) return false;
					if (!$this->_testDateMatch($ticket['date_archived'], $op, $choice)) return false;
					break;

				case self::TERM_DATE_RESOLVED:
					if (!$ticket['date_resolved']) return false;
					if (!$this->_testDateMatch($ticket['date_resolved'], $op, $choice)) return false;
					break;

				case self::TERM_DATE_LAST_AGENT_REPLY:
					if (!$ticket['date_last_agent_reply']) return false;
					if (!$this->_testDateMatch($ticket['date_last_agent_reply'], $op, $choice)) return false;
					break;

				case self::TERM_DATE_LAST_REPLY:
					$ts = $ticket->date_created;
					if ($ticket->date_last_agent_reply && $ticket->date_last_agent_reply->getTimestamp() > $ts) {
						$ts = $ticket->date_last_agent_reply;
					}
					if ($ticket->date_last_user_reply && $ticket->date_last_user_reply->getTimestamp() > $ts) {
						$ts = $ticket->date_last_user_reply;
					}
					if (!$this->_testDateMatch($ts, $op, $choice)) return false;
					break;

				case self::TERM_DATE_LAST_USER_REPLY:
					if (!$ticket['date_last_user_reply']) return false;
					if (!$this->_testDateMatch($ticket['date_last_user_reply'], $op, $choice)) return false;
					break;
                case 'time_created':
                case 'time_last_user_reply':
                    $field = str_replace('time', 'date', $term);

					$f = $ticket[$field];
					$ticket_time = clone $f;

					if (!empty($choice['timezone'])) {
						$ticket_time->setTimezone(new \DateTimeZone($choice['timezone']));
						$ticket_time = \Orb\Util\Dates::convertToUtcDateTime($ticket_time);
					}

					$time = clone $ticket_time;
                    $time->setTime($choice['hour1'], $choice['minute1']);

                    switch($op) {
                        case 'before':
                            return $ticket_time < $time;
                        case 'after':
                            return $ticket_time > $time;
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
				default:
					$terms = new TicketTerms(array(array(
						'type' => $term,
						'op' => $op,
						'options' => $choice
					)));
					if (!$terms->doesTicketMatch($ticket)) {
						return false;
					}
					break;
			}
		}

		if ($this->person_search) {
			if (!$this->person_search->doesPersontMatch($ticket->person, $ticket)) {
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
			case 'person': return 'person_id';
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
		$info = $this->findTerm('status');
		list($term, $op, $data) = $info;

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
