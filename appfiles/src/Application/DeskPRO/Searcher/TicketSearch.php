<?php

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
	const TERM_RECEIVING_GATEWAY         = 'receiving_gateway';
	const TERM_GATEWAY_ADDRESS           = 'email_gateway_address';
	const TERM_HOLD                      = 'is_hold';
	const TERM_FLAGGED                   = 'flagged';

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
	 * Are we using archive mode?
	 *
	 * @return bool
	 */
	public function isArchiveSearch()
	{
		return $this->is_archive;
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
	 * @return void
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
		$db = App::getDb();

		$ticket_ids = $db->fetchAllCol($this->getSql($pageinfo));

		return $ticket_ids;
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

		if (true || $this->isArchiveSearch()) {
			$table = 'tickets';
		} else {
			$table = 'tickets_search_active';
		}
		$table = 'tickets';

		$sql = "SELECT tickets.id FROM $table AS tickets ";

		#------------------------------
		# Standard for permissions
		#------------------------------

		if ($this->person AND $this->person['is_agent']) {

			$agent = $this->person;
			if ($agent AND $agent['is_agent']) {
				$agent->loadHelper('AgentPermissions');
				$agent->loadHelper('AgentTeam');

				// perms only matter if person has permissions applied at all
				if ($agent->getDisallowedDepartments()) {

					$ticket_parts['joins'] = array('tickets_participants_perm', "LEFT JOIN tickets_participants AS part_check ON (part_check.ticket_id = tickets.id)");

					$where_perm[] = "tickets.agent_id = {$agent['id']}";
					if ($agent->getAgentTeamIds()) {
						$where_perm[] = "tickets.agent_team_id IN (" . implode(',', $agent->getAgentTeamIds()) . ")";
					}

					$where_perm[] = "tickets.department_id IN (" . implode(',', $agent->getAllowedDepartments()) . ")";
					$where_perm[] = "part_check.person_id = {$agent['id']}";

					$where_perm = implode(' OR ', $where_perm);

					$where = "($where_perm) AND ";
				}
			}
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

		#------------------------------
		# Add wheres
		#------------------------------

		if (!empty($ticket_parts['wheres'])) {
			$where .= implode(" AND ", $ticket_parts['wheres']);
		}
		if (!empty($user_parts['wheres'])) {
			$where .= " AND " . implode(" AND ", $user_parts['wheres']);
		}

		if ($where) {
			$sql .= " WHERE $where ";
		}

		$sql .= " GROUP BY tickets.id ";
		$sql .= $order_by;

		if ($pageinfo) {
			$sql .= " LIMIT {$pageinfo['offset']}, {$pageinfo['limit']} ";
		} else {
			$sql .= " LIMIT 1000";
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

		$term_id = null;
		$m = null;
		if (preg_match('#^(.*?)\[(.*?)\]$#', $type, $m)) {
			$type = $m[1];
			$term_id = $m[2];
		}


		$order_by = '';

		switch ($type) {
			case 'ticket.urgency':
				$order_by = "ORDER BY tickets.urgency $dir";
				$this->order_summary = "Urgency";
				break;

			case 'ticket.date_created':
				$order_by = "ORDER BY tickets.id $dir";
				$this->order_summary = "Date created";
				break;

			case 'ticket.priority':
				$pris = App::getEntityRepository('DeskPRO:TicketPriority')->getIdsInOrder();
				if ($pris) {
					$order_by = "ORDER BY FIELD(tickets.priority_id, " . implode(',', $pris) . ")";
				} else {
					$order_by = "ORDER BY tickets.priority_id $dir";
				}
				$this->order_summary = "Priority";
				break;

			case 'ticket.date_resolved':
				$this->order_summary = "Date resolved";
				$order_by = "ORDER BY tickets.date_resolved $dir";
				break;

			case 'ticket.date_closed':
				$this->order_summary = "Date closed";
				$order_by = "ORDER BY tickets.date_closed $dir";
				break;

			case 'ticket.last_activity':
				$this->order_summary = "Last user activity";
				$order_by = "ORDER BY tickets.date_last_user_reply $dir";
				break;

			case 'ticket.organization':
				$this->order_summary = "Organization name";
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

			switch ($term) {
				case self::TERM_ID:
					if ($op == self::OP_IS) {
						$wheres[] = "$tickets_table.id IN (" . implode(',', (array)$choice) . ")";
					} else {
						$wheres[] = $this->_rangeMatch("$tickets_table.id", $op, $choice, true);
					}
					$this->summary[] = $this->_rangeSummary($tr->phrase('agent.id'), $op, $choice);
					break;
				case self::TERM_ARCHIVE_SEARCH:
					if ($choice) {
						$this->enableArchiveSearch();
					}
					break;
				case self::TERM_DEPARTMENT:
					$this->affected_fields[] = 'ticket.department_id';
					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.department'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:Department')->getDepartmentNames((array)$choice);
						return $titles;
					});

					if ($choice && (!is_array($choice) || !in_array('0', $choice))) {
						$choice = App::getEntityRepository('DeskPRO:Department')->getIdsInTree($choice, true);
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
					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.tickets.category'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:TicketCategory')->getCategoryNames((array)$choice);
						return $titles;
					});

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_CATEGORY;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.category_id", $op, $choice, true);
					break;
				case self::TERM_PRODUCT:
					$this->affected_fields[] = 'ticket.product_id';
					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.product'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:Product')->getProductNames((array)$choice);
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
					$this->summary[] = $this->_rangeSummary($tr->phrase('agent.tickets.urgency'), $op, $choice);
					$wheres[] = $this->_rangeMatch("$tickets_table.urgency", $op, $choice);
					break;
				case self::TERM_DATE_CREATED:
					$this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.tickets.date_created'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_created", $op, $choice);
					break;
				case self::TERM_DATE_RESOLVED:
					$this->affected_fields[] = 'ticket.date_resolved';
					$this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.tickets.date_resolved'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_resolved", $op, $choice);
					$wheres[] = $this->_choiceMatch("$tickets_table.status", $op, array('resolved'));
					break;
				case self::TERM_DATE_CLOSED:
					$this->affected_fields[] = 'ticket.date_closed';
					$this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.tickets.date_closed'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_closed", $op, $choice);
					$wheres[] = $this->_choiceMatch("$tickets_table.status", $op, array('closed'));
					break;
				case self::TERM_DATE_LAST_USER_REPLY:
					$this->affected_fields[] = 'ticket.date_last_user_reply';
					$this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.tickets.date_last_user_reply'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_last_user_reply", $op, $choice);
					break;
				case self::TERM_DATE_LAST_AGENT_REPLY:
					$this->affected_fields[] = 'ticket.date_last_agent_reply';
					$this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.tickets.date_last_agent_reply'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_last_agent_reply", $op, $choice);
					break;
				case self::TERM_WORKFLOW:
					$this->affected_fields[] = 'ticket.workflow_id';
					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.tickets.workflow'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:TicketWorkflow')->getWorkflowNames((array)$choice);
						return $titles;
					});

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_WORKFLOW;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.workflow_id", $op, $choice. true);
					break;
				case self::TERM_LANGUAGE:
					$this->affected_fields[] = 'ticket.language_id';
					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_LANGUAGE;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.language_id", $op, $choice, true);
					break;
				case self::TERM_AGENT:
					$this->affected_fields[] = 'ticket.agent_id';

					$info = $this->_normalizeAgentChoice($choice);
					$unassigned = $info['unassigned'];
					$agent_ids = $info['agent_ids'];
					$not_id = $info['not_id'];

					if ($unassigned) {
						$this->summary[] = $this->_choiceSummary($tr->phrase('agent.agent'), $op, $tr->phrase('agent.unassigned'));
						$wheres[] = "$tickets_table.agent_id IS NULL";
					} else {
						if ($agent_ids) {
							$this->summary[] = $this->_choiceSummary($tr->phrase('agent.agent'), $op, $agent_ids, function($choice) {
								$titles = App::getEntityRepository('DeskPRO:Person')->getAgentNames((array)$choice);
								return $titles;
							});

							if (count($agent_ids) == 1) {
								$this->specific_fields[] = self::TERM_AGENT;
							}

							$wheres[] = $this->_choiceMatch("$tickets_table.agent_id", $op, $agent_ids, true);
						}

						if ($not_id) {
							$this->summary[] = $tr->phrase('agent.agent_is_not_me');
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
						$this->summary[] = $this->_choiceSummary($tr->phrase('agent.agent_team'), $op, $tr->phrase('agent.unassigned'));

					} else {
						if ($team_ids) {
							$this->summary[] = $this->_choiceSummary($tr->phrase('agent.agent_team'), $op, $team_ids, function($choice) {
								$titles = App::getEntityRepository('DeskPRO:AgentTeam')->getTeamNames((array)$choice);
								return $titles;
							});

							if (count($choice) == 1) {
								$this->specific_fields[] = self::TERM_AGENT_TEAM;
							}

							$wheres[] = $this->_choiceMatch("$tickets_table.agent_team_id", $op, $team_ids, true);
						}

						if ($not_ids) {
							$this->summary[] = $this->_choiceSummary($tr->phrase('agent.agent_team'), 'not', $not_ids, function($choice) {
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

					$choice_str = array();
					foreach ((array)$choice as $c) {
						$choice_str[] = $tr->phrase('agent.tickets.status_' . $c);
					}
					$choice_str = implode(', ', $choice_str);

					$phrase = 'agent.x_is_y';
					if ($op == self::OP_NOT OR $op == self::OP_NOTCONTAINS) {
						$phrase = 'agent.x_is_not_y';
					}
					$this->summary[] = $tr->phrase($phrase, array('field' => $tr->phrase('agent.tickets.status'), 'value' => $choice_str));

					$archive_statuses = array_filter((array)$choice, function($val) {
						if ($val != 'awaiting_agent' AND $val != 'awaiting_user') {
							return true;
						}

						return false;
					});

					if ($archive_statuses) {
						$this->enableArchiveSearch();
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.status", $op, $choice);
					break;
				case self::TERM_HIDDEN_STATUS:
					$this->affected_fields[] = 'ticket.hidden_status';

					$choice_str = array();
					foreach ((array)$choice as $c) {
						$choice_str[] = $tr->phrase('agent.tickets.hidden_status_' . $c);
					}
					$choice_str = implode(', ', $choice_str);

					$this->summary[] = $tr->phrase('agent.x_is_y', array('field' => $tr->phrase('agent.tickets.status'), 'value' => $choice_str));

					$wheres[] = $this->_choiceMatch("$tickets_table.hidden_status", $op, $choice);

					break;
				case self::TERM_HOLD:

					$this->affected_fields[] = 'ticket.is_hold';

					// Op is irrelevant. or, it's always "is", and choice is yes/no

					if ($choice) {
						$wheres[] = "tickets.is_hold = 1";
					} else {
						$wheres[] = "tickets.is_hold = 0";
					}

					$this->summary[] = $tr->phrase('agent.is_not_x', array('field' => 'on hold'));

					break;
				case self::TERM_ORGANIZATION:
					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.tickets.organization'), $op, $choice);

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

					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.tickets.participants'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:Person')->getAgentNames((array)$choice);
						return $titles;
					}, true);

					$wheres[] = $this->_choiceMatch($field, $op, $choice);
					break;
				case self::TERM_SUBJECT:
					$this->affected_fields[] = 'ticket.subject';
					$field = 'tickets.subject';
					if (!$this->is_archive) {
						$joins[] = 'tickets_search_subjects';
						$field = 'tickets_search_subjects.subject';
					}

					if ($op == self::OP_IS) {
						$this->summary[] = $tr->phrase('agent.x_is_y', array('field' => $tr->phrase('agent.tickets.subject'), 'value' => $choice));
					} else {
						$this->summary[] = $tr->phrase('agent.x_is_not_y', array('field' => $tr->phrase('agent.tickets.subject'), 'value' => $choice));
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

					$this->summary[] = $this->_choiceSummary($tr->phrase('agent.label'), $op, $choice);

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

					switch ($search_type) {
						case 'input':
						case 'value':

							if ($op == self::OP_IS) {
								$this->summary[] = $tr->phrase('agent.x_is_y', array('field' => $field['title'], 'value' => $choice));
							} else {
								$this->summary[] = $tr->phrase('agent.x_is_not_y', array('field' => $field['title'], 'value' => $choice));
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
								$this->summary[] = $tr->phrase('agent.x_is_y', array('field' => $field['title'], 'value' => $choice_str));
							} else {
								$this->summary[] = $tr->phrase('agent.x_is_not_y', array('field' => $field['title'], 'value' => $choice_str));
							}

							$field = 'custom_data_ticket_'.$join_id.'.field_id';
							switch ($op) {
								case self::OP_CONTAINS:
								case self::OP_IS:
									$joins[] = array(
										'custom_data_ticket',
										"LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id)"
									);
									$wheres[] = "$field IN ($choices_in)";
									break;

								case self::OP_NOTCONTAINS:
								case self::OP_NOT:
									$joins[] = array(
										'custom_data_ticket',
										"LEFT JOIN AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND custom_data_ticket_$join_id.field_id IN ($choices_in)"
									);
									$wheres[] = "$field IS NULL";
									break;
							}
							break;
					}
					break; // end break TERM_TICKET_FIELD

				case self::TERM_USER_WAITING:
					$this->affected_fields[] = 'ticket.date_user_waiting';
					$wheres[] = $this->_dateMatch("$tickets_table.date_user_waiting", $op, $choice);
					break;

				case self::TERM_AGENT_WAITING:
					$this->affected_fields[] = 'ticket.date_agent_waiting';
					$wheres[] = $this->_dateMatch("$tickets_table.date_agent_waiting", $op, $choice);
					break;

				case self::TERM_TOTAL_USER_WAITING:
					$this->affected_fields[] = 'ticket.total_user_waiting';
					$wheres[] = $this->_rangeMatch("$tickets_table.total_user_waiting", $op, $choice);
					break;

				case self::TERM_CREATION_SYSTEM:
					$set_status = true;
					$this->summary[] = $tr->phrase('agent.x_is_y', array(
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

					$wheres[] = $this->_choiceMatch("$tickets_table.email_gateway_id", $op, $choice. true);
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

	protected function _normalizeAgentChoice($choice)
	{
		$choice = (array)$choice;

		$agent_ids = array();
		$not_id = null;
		$unassigned = false;

		foreach ($choice as $c) {
			$c = (int)$c;
			if ($c === 0) {
				$unassigned = true;
				break;
			} elseif ($c == -1) {
				if ($this->getPersonContext()) {
					$agent_ids[] = $this->getPersonContext()->getId();
				} else {
					$agent_ids[] = -1;
				}
			} elseif ($c == -2) {
				if ($this->getPersonContext()) {
					$not_id = $this->getPersonContext()->getId();
				} else {
					$not_id = -1;
				}
			} else {
				$agent_ids = $c;
			}
		}

		return array(
			'agent_ids' => $agent_ids,
			'not_id' => $not_id,
			'unassigned' => $unassigned
		);
	}

	protected function _normalizeAgentTeamChoice($choice)
	{
		$choice = (array)$choice;

		$team_ids = array();
		$not_ids = null;
		$no_team = false;

		if ($this->getPersonContext()) {
			$agent = $this->getPersonContext();
			$agent->loadHelper('AgentTeam');
		} else {
			$agent = null;
		}

		foreach ($choice as $c) {
			$c = (int)$c;
			if ($c === 0) {
				$no_team = true;
				break;
			} elseif ($c == -1) {
				if ($agent) {
					$team_ids = Arrays::removeFalsey($agent->getAgentTeamIds());
				} else {
					$team_ids = array();
				}
				$team_ids[] = -1;
			} elseif ($c == -2) {
				if ($agent) {
					$not_ids = Arrays::removeFalsey($agent->getAgentTeamIds());
				} else {
					$not_ids = array();
				}
				$not_ids[] = -1;
			} else {
				$team_ids = $c;
			}
		}

		return array(
			'team_ids' => $team_ids,
			'not_ids' => $not_ids,
			'no_team' => $no_team
		);
	}


	/**
	 * Check a specific ticket against these terms to see if it matches.
	 *
	 * @param Ticket $ticket
	 * @return bool
	 */
	public function doesTicketMatch(Entity\Ticket $ticket)
	{
		foreach ($this->terms as $term => $info) {
			list($op, $choice) = $info;

			switch ($term) {
				case self::TERM_STATUS:
					if (!$this->_testChoiceMatch($ticket['status'], $op, $choice)) return false;
					break;

				case self::TERM_DEPARTMENT:
					if (count($choice) == 1) $choice = Arrays::getFirstItem($choice);
					$choice = App::getEntityRepository('DeskPRO:Department')->getIdsInTree($choice, true);

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

				case self::TERM_PARTICIPANT:

					$info = $this->_normalizeAgentChoice($choice);
					$agent_ids = $info['agent_ids'];

					if ($agent_ids) {
						$participant_ids = array();
						foreach ($ticket->getRawParticipants() as $part) {
							$participant_ids[] = $part->person->id;
						}

						$any = false;
						foreach ($participant_ids as $pid) {
							if ($this->_testChoiceMatch($pid, $op, $agent_ids)) {
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
			}
		}

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
			default: return false;
		}
	}
}
