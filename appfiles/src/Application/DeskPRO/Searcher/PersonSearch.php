<?php

namespace Application\DeskPRO\Searcher;

use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;

use \Application\CoreBundle\Entity;

class PersonSearch extends SearcherAbstract
{
	const TERM_ORGANIZATION   = 'organization';
	const TERM_LANGUAGE       = 'language';
	const TERM_EMAIL          = 'email';
	const TERM_NAME           = 'name';

	/**
	 * True to search in the non-search tables (aka all tickets not just active)
	 * @var bool
	 */
	protected $is_archive = false;


	
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

		$people_ids = $db->fetchAllCol($this->getSql());

		return $people_ids;
	}



	/**
	 * Get the SQL query that'll fetch the results
	 * @return string
	 */
	public function getSql()
	{
		if ($this->is_archive) {
			$table = 'people';
		} else {
			$table = 'people_search';
		}

		$sql = "SELECT $table.id FROM $table ";

		$parts = $this->getSqlParts();


		#------------------------------
		# Add joins
		#------------------------------

		foreach ($parts['joins'] as $j) {
			$sql .= "LEFT JOIN $j ON $j.ticket_id = $table.id ";
		}

		#------------------------------
		# Add wheres
		#------------------------------

		$sql .= "WHERE ";
		$sql .= implode(" AND ", $parts['wheres']);
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
		$people_table = 'people_search';
		if ($this->is_archive) {
			$people_table = 'people';
		}

		$db = App::getDb();

		$wheres = array();
		$joins = array();

		foreach ($this->terms as $term => $info) {
			list($op, $choice) = $info;

			switch ($term) {
				case self::TERM_LANGUAGE:
					$wheres[] = $this->_choiceMatch("$people_table.language_id", $op, $choice);
					break;
				case self::TERM_ORGANIZATION:
					$wheres[] = $this->_choiceMatch("$people_table.organization_id", $op, $choice);
					break;
				case self::TERM_EMAIL:
					if (!$this->is_archive) {
						$table = 'people_emails_search';
					} else {
						$table = 'people_emails';
					}

					$joins[] = $table;

					switch ($op) {
						case self::OP_CONTAINS:
						case self::OP_NOTCONTAINS:
							$op = 'LIKE';
							if ($op == self::OP_NOTCONTAINS) $op = 'NOT LIKE';
							$wheres[] = "$table.email $op " . $db->quote('%'.$choice.'%');
							break;
					}

					$wheres[] = $this->_choiceMatch("$table.email", $op, $choice);
					break;
				case self::TERM_NAME:
					switch ($op) {
						case self::OP_CONTAINS:
						case self::OP_NOTCONTAINS:
							$op = 'LIKE';
							if ($op == self::OP_NOTCONTAINS) $op = 'NOT LIKE';
							$wheres[] = "$people_table.name $op " . $db->quote('%'.$choice.'%');
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
	 * Check a specific person against these terms to see if it matches.
	 *
	 * @param Person $person
	 * @return bool
	 */
	public function doesPersontMatch(Entity\Person $person)
	{
		foreach ($this->terms as $term => $info) {
			list($op, $choice) = $info;

			switch ($term) {
				case self::TERM_ORGANIZATION:
					if (!$this->_testChoiceMatch($person['organization_id'], $op, $choice)) return false;
					break;
				case self::TERM_LANGUAGE:
					if (!$this->_testChoiceMatch($person['language_id'], $op, $choice)) return false;
					break;
				case self::TERM_NAME:
					switch ($op) {
						case self::OP_CONTAINS:
							if (strpos(strtolower($person['name']), strtolower($choice)) === false) return false;
							break;
						case self::OP_NOTCONTAINS:
							if (strpos(strtolower($person['name']), strtolower($choice)) !== false) return false;
							break;
					}
					break;
				case self::TERM_EMAIL:
					$any = false;
					foreach ($person['emails'] as $email) {
						if (strpos(strtolower($email['email']), strtolower($choice)) !== false) {
							$any = true;
							if ($op == self::OP_NOTCONTAINS) {
								return false;
							}
						}
					}

					if ($op == self::OP_CONTAINS AND !$any) {
						return false;
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