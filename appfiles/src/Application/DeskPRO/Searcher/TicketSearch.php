<?php

namespace Application\DeskPRO\Searcher;

use \Application\DeskPRO\App;

use \Orb\Util\Util;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

class TicketSearch extends SearcherAbstract
{
	const TERM_ID                        = 'id';
	const TERM_DEPARTMENT                = 'department';
	const TERM_CATEGORY                  = 'category';
	const TERM_PRODUCT                   = 'product';
	const TERM_AGENT                     = 'agent';
	const TERM_AGENT_TEAM                = 'agent_team';
	const TERM_STATUS                    = 'status';
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
	const TERM_DATE_LAST_USER_REPLY      = 'date_last_user_reply';
	const TERM_DATE_LAST_AGENT_REPLY     = 'date_last_agent_reply';
	const TERM_URGENCY                   = 'urgency';
	const TERM_USER_WAITING              = 'user_waiting';
	const TERM_AGENT_WAITING             = 'agent_waiting';
	const TERM_ARCHIVE_SEARCH            = 'archive_search';
	const TERM_DELETED                   = 'deleted';
	const TERM_CREATION_SYSTEM           = 'creation_system';
	const TERM_RECEIVING_GATEWAY         = 'receiving_gateway';

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
	protected $summary = null;

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
		return $this->summary;
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

		if ($this->isArchiveSearch()) {
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

		if ($user_parts AND $user_parts['joins']) {
			$sql .= "INNER JOIN people ON (people.id = tickets.person_id) ";

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
				break;

			case 'ticket.date_created':
				$order_by = "ORDER BY tickets.id $dir";
				break;

			case 'ticket.priority':
				$pris = App::getEntityRepository('DeskPRO:TicketPriority')->getIdsInOrder();
				if ($pris) {
					$order_by = "ORDER BY FIELD(tickets.priority_id, " . implode(',', $pris) . ")";
				} else {
					$order_by = "ORDER BY tickets.priority_id $dir";
				}
				break;

			case 'ticket.date_resolved':
				$order_by = "ORDER BY tickets.date_resolved $dir";
				break;

			case 'ticket.date_closed':
				$order_by = "ORDER BY tickets.date_closed $dir";
				break;

			case 'ticket.last_activity':
				$order_by = "ORDER BY tickets.date_last_user_reply $dir";
				break;

			case 'ticket.organization':
				$order_by = array(
					"INNER JOIN organizations AS sort_table ON (sort_table.id = tickets.organization_id)",
					"ORDER BY sort_table.name $dir"
				);
				break;

			case 'ticket.ticket_field':
				$field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($term_id);
				if (!$field) break;

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
					$wheres[] = $this->_rangeMatch("$tickets_table.id", $op, $choice, true);
					$this->summary[] = $this->_rangeSummary($tr->phrase('core.id'), $op, $choice);
					break;
				case self::TERM_ARCHIVE_SEARCH:
					if ($choice) {
						$this->enableArchiveSearch();
					}
					break;
				case self::TERM_DEPARTMENT:
					$this->summary[] = $this->_choiceSummary($tr->phrase('core.department'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:Department')->getDepartmentNames((array)$choice);
						return $titles;
					});

					$choice = App::getEntityRepository('DeskPRO:Department')->getIdsInTree($choice, true);

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_DEPARTMENT;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.department_id", $op, $choice, true);
					break;
				case self::TERM_DELETED:
					$set_status = true;
					$this->summary[] = $tr->phrase('core_tickets.ticket_is_deleted');
					$wheres[] = $this->_choiceMatch("$tickets_table.status", self::OP_IS, 'hidden');
					$wheres[] = $this->_choiceMatch("$tickets_table.hidden_status", self::OP_IS, 'deleted');

					$this->enableArchiveSearch();

					break;
				case self::TERM_CATEGORY:
					$this->summary[] = $this->_choiceSummary($tr->phrase('core_tickets.category'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:TicketCategory')->getCategoryNames((array)$choice);
						return $titles;
					});

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_CATEGORY;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.category_id", $op, $choice, true);
					break;
				case self::TERM_PRODUCT:
					$this->summary[] = $this->_choiceSummary($tr->phrase('core.product'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:Product')->getProductNames((array)$choice);
						return $titles;
					});

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_PRODUCT;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.product_id", $op, $choice, true);
					break;
				case self::TERM_PRIORITY:

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_PRIORITY;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.priority_id", $op, $choice, true);
					break;
				case self::TERM_URGENCY:
					$this->summary[] = $this->_rangeSummary($tr->phrase('core_tickets.urgency'), $op, $choice);
					$wheres[] = $this->_rangeMatch("$tickets_table.urgency", $op, $choice);
					break;
				case self::TERM_DATE_CREATED:
					$this->summary[] = $this->_rangeSummary($tr->phrase('core_tickets.date_created'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_created", $op, $choice);
					break;
				case self::TERM_DATE_RESOLVED:
					$this->summary[] = $this->_rangeSummary($tr->phrase('core_tickets.date_resolved'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_resolved", $op, $choice);
					$wheres[] = $this->_choiceMatch("$tickets_table.status", $op, array('resolved', 'closed'));
					break;
				case self::TERM_DATE_LAST_USER_REPLY:
					$this->summary[] = $this->_rangeSummary($tr->phrase('core_tickets.date_last_user_reply'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_last_user_reply", $op, $choice);
					break;
				case self::TERM_DATE_LAST_AGENT_REPLY:
					$this->summary[] = $this->_rangeSummary($tr->phrase('core_tickets.date_last_agent_reply'), $op, $choice);
					$wheres[] = $this->_dateMatch("$tickets_table.date_last_agent_reply", $op, $choice);
					break;
				case self::TERM_WORKFLOW:
					$this->summary[] = $this->_choiceSummary($tr->phrase('core_tickets.workflow'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:TicketWorkflow')->getWorkflowNames((array)$choice);
						return $titles;
					});

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_WORKFLOW;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.workflow_id", $op, $choice. true);
					break;
				case self::TERM_LANGUAGE:

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_LANGUAGE;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.language_id", $op, $choice, true);
					break;
				case self::TERM_AGENT:
					if ($choice == 0) {
						$this->summary[] = $this->_choiceSummary($tr->phrase('core.agent'), $op, $tr->phrase('core.unassigned'));
						$wheres[] = "$tickets_table.agent_id IS NULL";
					} elseif ($choice == -1) {
						$this->summary[] = $tr->phrase('core.agent_is_me');
						$wheres[] = "$tickets_table.agent_id = " . App::getCurrentPerson()->getId();

						$this->specific_fields[] = self::TERM_AGENT;

					} elseif ($choice == -2) {
						$this->summary[] = $tr->phrase('core.agent_is_not_me');
						$wheres[] = "$tickets_table.agent_id != " . App::getCurrentPerson()->getId();
					} else {
						$this->summary[] = $this->_choiceSummary($tr->phrase('core.agent'), $op, $choice, function($choice) {
							$titles = App::getEntityRepository('DeskPRO:Person')->getAgentNames((array)$choice);
							return $titles;
						});

						if (count($choice) == 1) {
							$this->specific_fields[] = self::TERM_AGENT;
						}

						$wheres[] = $this->_choiceMatch("$tickets_table.agent_id", $op, $choice, true);
					}
					break;
				case self::TERM_AGENT_TEAM:
					if ($choice == 0) {
						$wheres[] = "$tickets_table.agent_team_id IS NULL";

						$this->summary[] = $this->_choiceSummary($tr->phrase('core.agent_team'), $op, $tr->phrase('core.unassigned'));
					} elseif ($choice == -1) {
						$person = App::getCurrentPerson();
						$person->loadHelper('AgentTeam');
						$team_ids = $person->getAgentTeamIds();
						if ($team_ids) {
							$this->summary[] = $tr->phrase('core.my_agent_team');
							$wheres[] = $this->_choiceMatch("$tickets_table.agent_team_id", $op, $team_ids, true);
						}
					} else {
						$this->summary[] = $this->_choiceSummary($tr->phrase('core.agent_team'), $op, $choice, function($choice) {
							$titles = App::getEntityRepository('DeskPRO:AgentTeam')->getTeamNames((array)$choice);
							return $titles;
						});

						if (count($choice) == 1) {
							$this->specific_fields[] = self::TERM_AGENT_TEAM;
						}

						$wheres[] = $this->_choiceMatch("$tickets_table.agent_team_id", $op, $choice, true);
					}
					break;
				case self::TERM_STATUS:
					$set_status = true;
					$this->summary[] = $tr->phrase('core.x_is_y', array('field' => $tr->phrase('core_tickets.status'), 'value' => $tr->phrase('core_tickets.status_' . $choice)));

					$archive_statuses = array_filter((array)$choice, function($val) {
						if ($val != 'open' AND $val != 'pending') {
							return true;
						}

						return false;
					});

					if ($archive_statuses) {
						$this->enableArchiveSearch();
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.status", $op, $choice);
					break;
				case self::TERM_ORGANIZATION:
					$this->summary[] = $this->_choiceSummary($tr->phrase('core_tickets.organization'), $op, $choice);

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_ORGANIZATION;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.organization", $op, $choice, true);
					break;
				case self::TERM_PARTICIPANT:
					$joins[] = 'tickets_participants';
					$field = 'tickets_participants.person_id';

					$this->summary[] = $this->_choiceSummary($tr->phrase('core_tickets.participants'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:AgentTeam')->getTeamNames((array)$choice);
						return $titles;
					});

					$wheres[] = $this->_choiceMatch($field, $op, $choice);
					break;
				case self::TERM_SUBJECT:
					$field = 'tickets.subject';
					if (!$this->is_archive) {
						$joins[] = 'tickets_search_subjects';
						$field = 'tickets_search_subjects.subject';
					}

					if ($op == self::OP_IS) {
						$this->summary[] = $tr->phrase('core.x_is_y', array('field' => $tr->phrase('core_tickets.subject'), 'value' => $choice));
					} else {
						$this->summary[] = $tr->phrase('core.x_is_not_y', array('field' => $tr->phrase('core_tickets.subject'), 'value' => $choice));
					}
					$wheres[] = $this->_stringMatch($field, $op, $choice);
					break;

				case self::TERM_LABEL:

					$this->_normalizeOpAndChoice($op, $choice);

					$choices_in = array();
					if (is_array($choice)) {
						foreach ((array)$choice as $c) {
							$choices_in[] = $db->quote($c);
						}
						$choices_in = implode(',', $choices_in);
					}

					$this->summary[] = $this->_choiceSummary($tr->phrase('core.label'), $op, $choice);

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

					$search_type = $field->getHandler()->getSearchType();

					switch ($search_type) {
						case 'input':
						case 'value':

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
							break;

						case 'id':
							$join_id = Util::requestUniqueId();
							$choices_in = array();
							foreach ((array)$choice as $c) {
								$choices_in[] = (int)$c;
							}
							$choices_in = implode(',', $choices_in);

							$field = 'custom_data_ticket_'.$join_id.'.field_id';
							switch ($op) {
								case self::OP_CONTAINS:
									$joins[] = array(
										'custom_data_ticket',
										"LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id)"
									);
									$wheres[] = "$field IN ($choices_in)";
									break;

								case self::OP_NOTCONTAINS:
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
					$wheres[] = $this->_dateMatch("$tickets_table.date_user_waiting", $op, $choice);
					$wheres[] = $this->_choiceMatch("$tickets_table.status", $op, array('open'));
					break;

				case self::TERM_AGENT_WAITING:
					$wheres[] = $this->_dateMatch("$tickets_table.date_agent_waiting", $op, $choice);
					$wheres[] = $this->_choiceMatch("$tickets_table.status", $op, array('pending'));
					break;

				case self::TERM_CREATION_SYSTEM:
					$set_status = true;
					$this->summary[] = $tr->phrase('core.x_is_y', array(
						'field' => $tr->phrase('core_tickets.creation_system'),
						'value' => $tr->phrase('core_tickets.creation_system_' . str_replace('.', '_', $choice))
					));
					$wheres[] = $this->_stringMatch("$tickets_table.creation_system", $op, $choice, true, true);
					break;
				case self::TERM_RECEIVING_GATEWAY:
					$this->summary[] = $this->_choiceSummary($tr->phrase('core_tickets.receiving_gateway'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:EmailGateway')->getGatewayNames((array)$choice);
						return $titles;
					});

					if (count($choice) == 1) {
						$this->specific_fields[] = self::TERM_RECEIVING_GATEWAY;
					}

					$wheres[] = $this->_choiceMatch("$tickets_table.email_gateway_id", $op, $choice. true);
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
				case self::TERM_DEPARTMENT:
					$choice = App::getEntityRepository('DeskPRO:Department')->getIdsInTree($choice, true);
					if (count($choice) == 1) $choice = $choice[0];

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
					if (!$this->_testChoiceMatch($ticket['agent_id'], $op, $choice)) return false;
					break;
				case self::TERM_PARTICIPANT:
					if (is_array($choice)) {
						$participant_ids = $ticket->getParticipantIds();
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
						if ($ticket->hasParticipant($choice)) {
							if ($op == self::OP_NOT) return false;
						} else {
							if ($op == self::OP_IS) return false;
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
				
				case self::TERM_RECEIVING_GATEWAY:
					if (!$this->_testChoiceMatch($ticket['email_gateway_id'], $op, $choice. true)) return false;
					break;
			}
		}

		return true;
	}
}