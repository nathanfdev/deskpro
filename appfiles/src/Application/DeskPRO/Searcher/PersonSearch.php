<?php

namespace Application\DeskPRO\Searcher;

use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

class PersonSearch extends SearcherAbstract
{
	const TERM_ORGANIZATION   = 'organization';
	const TERM_LANGUAGE       = 'language';
	const TERM_EMAIL          = 'email';
	const TERM_EMAIL_DOMAIN   = 'email_domain';
	const TERM_NAME           = 'name';
	const TERM_PERSON_FIELD   = 'person_field';
	const TERM_LABEL          = 'label';


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
		$sql = "SELECT people.id FROM $table ";

		$parts = $this->getSqlParts();


		#------------------------------
		# Add joins
		#------------------------------

		foreach ($parts['joins'] as $j) {
			if (is_array($j)) {
				$sql .= $j[1] . " ";
			} else {
				$sql .= "LEFT JOIN $j ON $j.person_id = people.id ";
			}
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
					$joins[] = 'people_emails';

					switch ($op) {
						case self::OP_IS:
						case self::OP_NOT:
							$wheres[] = "$table.email $op " . $db->quote($choice);
							break;

						case self::OP_CONTAINS:
						case self::OP_NOTCONTAINS:
							$op = 'LIKE';
							if ($op == self::OP_NOTCONTAINS) $op = 'NOT LIKE';
							$wheres[] = "$table.email $op " . $db->quote('%'.$choice.'%');
							break;
					}
					break;
				case self::TERM_EMAIL_DOMAIN:
					$joins[] = 'people_emails';

					switch ($op) {
						case self::OP_IS:
						case self::OP_NOT:
							$wheres[] = "$table.email_domain $op " . $db->quote($choice);
							break;

						case self::OP_CONTAINS:
						case self::OP_NOTCONTAINS:
							$op = 'LIKE';
							if ($op == self::OP_NOTCONTAINS) $op = 'NOT LIKE';
							$wheres[] = "$table.email_domain $op " . $db->quote('%'.$choice.'%');
							break;
					}
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

				case self::TERM_LABEL:
					$field = 'labels_people.label';

					$choices_in = array();
					foreach ((array)$choice as $c) {
						$choices_in[] = $db->quote($c);
					}
					$choices_in = implode(',', $choices_in);

					switch ($op) {
						case self::OP_IS:
							$joins[] = 'labels_people';
							$wheres[] = "$field = " . $db->quote($choice);
							break;
						case self::OP_NOT:
							$joins[] = array(
								'labels_people',
								'LEFT JOIN labels_people ON (labels_people.person_id = people.id AND labels_people.label = '.$db->quote($choice).')'
							);
							$wheres[] = "$field IS NULL";
							break;
						case self::OP_CONTAINS:
							$joins[] = 'labels_people';
							$wheres[] = "$field IN ($choices_in)";
							break;

						case self::OP_NOTCONTAINS:
							$joins[] = array(
								'labels_people',
								"LEFT JOIN labels_people ON (labels_people.person_id = people.id AND labels_people.label IN ($choices_in)"
							);
							$wheres[] = "$field IS NULL";
							break;
					}
					break;

				case self::TERM_PERSON_FIELD:

					$field = App::getEntityRepository('DeskPRO:CustomDefPerson')->find($term_id);
					if (!$field) break;

					$search_type = $field->getHandler()->getSearchType();

					switch ($search_type) {
						case 'input':
						case 'value':

							$join_id = Util::requestUniqueId();
							$joins[] = array(
								'custom_data_person',
								"LEFT JOIN custom_data_person AS custom_data_person_$join_id ON (custom_data_person_$join_id.person_id = people.id AND custom_data_person_$join_id.field_id = $term_id)"
							);

							$field = 'custom_data_person_'.$join_id.'.'.$search_type;
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

							$field = 'custom_data_person_'.$join_id.'.field_id';
							switch ($op) {
								case self::OP_CONTAINS:
									$joins[] = array(
										'custom_data_person',
										"LEFT JOIN custom_data_person AS custom_data_person_$join_id ON (custom_data_person_$join_id.person_id = people.id)"
									);
									$wheres[] = "$field IN ($choices_in)";
									break;

								case self::OP_NOTCONTAINS:
									$joins[] = array(
										'custom_data_person',
										"LEFT JOIN AS custom_data_person_$join_id ON (custom_data_person_$join_id.person_id = people.id AND custom_data_person_$join_id.field_id IN ($choices_in)"
									);
									$wheres[] = "$field IS NULL";
									break;
							}
							break;
					}
					break; // end TERM_PERSON_FIELD
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