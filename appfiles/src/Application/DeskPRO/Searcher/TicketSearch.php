<?php

namespace Application\DeskPRO\Searcher;

use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

class TicketSearch extends SearcherAbstract
{
	const TERM_DEPARTMENT    = 'department';
	const TERM_CATEGORY      = 'category';
	const TERM_PRODUCT       = 'product';
	const TERM_AGENT         = 'agent';
	const TERM_STATUS        = 'status';
	const TERM_PRIORITY      = 'priority';
	const TERM_SUBJECT       = 'subject';
	const TERM_ORGANIZATION  = 'organization';
	const TERM_LANGUAGE      = 'language';
	const TERM_PARTICIPANT   = 'participant';

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
	 * Run the search and return an array of matching ID's.
	 *
	 * @param int $limit
	 * @return array
	 */
	public function getMatches()
	{
		$db = App::getDb();

		$ticket_ids = $db->fetchAllCol($this->getSql());

		return $ticket_ids;
	}


	
	/**
	 * Get the SQL query that'll fetch the results
	 * @return string
	 */
	public function getSql()
	{
		if ($this->is_archive) {
			$table = 'tickets';
			if ($this->person_search) {
				$this->person_search->enableArchiveSearch();
			}
			$u_table = 'people_search';
		} else {
			$table = 'tickets_search';
		}

		$sql = "SELECT $table.id FROM $table ";

		$ticket_parts = $this->getSqlParts();
		$user_parts = null;
		if ($this->person_search) {
			$user_parts = $this->person_search->getSqlParts();
		}


		#------------------------------
		# Add joins
		#------------------------------

		foreach ($ticket_parts['joins'] as $j) {
			$sql .= "LEFT JOIN $j ON $j.ticket_id = $table.id ";
		}

		if ($user_parts) {
			$sql .= "LEFT JOIN $u_table ON $u_table.id = $table.person_id ";
			foreach ($user_parts['joins'] as $j) {
				$sql .= "LEFT JOIN $j ON $j.person_id = $u_table.id ";
			}
		}

		#------------------------------
		# Add wheres
		#------------------------------

		$where = '';
		if ($ticket_parts['wheres']) {
			$where .= implode(" AND ", $ticket_parts['wheres']);
		}
		if ($user_parts) {
			$where .= " AND " . implode(" AND ", $user_parts['wheres']);
		}

		if ($where) {
			$sql .= " WHERE $where ";
		}

		$sql .= " LIMIT 1000";

		return $sql;
	}


	/**
	 * Get the SQL parts we need in the query.
	 *
	 * @return array
	 */
	public function getSqlParts()
	{
		$tickets_table = 'tickets_search';
		if ($this->is_archive) {
			$tickets_table = 'tickets';
		}

		$db = App::getDb();

		$wheres = array();
		$joins = array();

		foreach ($this->terms as $term => $info) {
			list($op, $choice) = $info;

			switch ($term) {
				case self::TERM_DEPARTMENT:
					$wheres[] = $this->_choiceMatch("$tickets_table.department_id", $op, $choice);
					break;
				case self::TERM_CATEGORY:
					$wheres[] = $this->_choiceMatch("$tickets_table.category_id", $op, $choice);
					break;
				case self::TERM_PRODUCT:
					$wheres[] = $this->_choiceMatch("$tickets_table.product_id", $op, $choice);
					break;
				case self::TERM_PRIORITY:
					$wheres[] = $this->_choiceMatch("$tickets_table.product_id", $op, $choice);
					break;
				case self::TERM_LANGUAGE:
					$wheres[] = $this->_choiceMatch("$tickets_table.language_id", $op, $choice);
					break;
				case self::TERM_AGENT:
					if ($op == self::OP_IS) {
						if ($choice == 0) {
							$wheres[] = "$tickets_table.agent_id IS NULL";
						} elseif ($choice == -1) {
							$wheres[] = "$tickets_table.agent_id != " . App::getCurrentPerson();
						} else {
							$wheres[] = $this->_choiceMatch("$tickets_table.agent_id", $op, $choice);
						}
					} else {
						$wheres[] = $this->_choiceMatch("$tickets_table.agent_id", $op, $choice);
					}
					break;
				case self::TERM_STATUS:
					$wheres[] = $this->_choiceMatch("$tickets_table.status", $op, $choice);
					break;
				case self::TERM_ORGANIZATION:
					$wheres[] = $this->_choiceMatch("$tickets_table.organization", $op, $choice);
					break;
				case self::TERM_PARTICIPANT:
					if (!$this->is_archive) {
						$joins[] = 'tickets_search_participants';
						$field = 'tickets_search_participants.person_id';
					} else {
						$joins[] = 'tickets_participants';
						$field = 'tickets_participants.person_id';
					}

					$wheres[] = $this->_choiceMatch($field, $op, $choice);
					break;
				case self::TERM_SUBJECT:
					$field = 'tickets.subject';
					if (!$this->is_archive) {
						$joins[] = 'tickets_search_subjects';
						$field = 'tickets_search_subjects.subject';
					}

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
			}
		}

		$joins = array_unique($joins);

		return array(
			'joins' => $joins,
			'wheres' => $wheres
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
				case self::TERM_DEPARTMENT:
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
			}
		}

		return true;
	}

	protected function _testChoiceMatch($value, $op, $choice)
	{
		if (is_array($choice)) {
			if ($op == self::OP_IS) {
				return in_array($value, $choice);
			} elseif ($op == self::OP_NOT) {
				return !in_array($value, $choice);
			}
		} else {
			if ($op == self::OP_IS) {
				return $value == $choice;
			} elseif ($op == self::OP_NOT) {
				return $value != $choice;
			}
		}
	}
}