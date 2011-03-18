<?php

namespace Application\DeskPRO\Searcher;

use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

class OrganizationSearch extends SearcherAbstract
{
	const TERM_ID             = 'id';
	const TERM_ORGANIZATION   = 'name';
	const TERM_NAME           = 'name';
	const TERM_ORGANIZATION_FIELD   = 'organization_field';
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

		$org_ids = $db->fetchAllCol($this->getSql());

		return $org_ids;
	}



	/**
	 * Get the SQL query that'll fetch the results
	 * @return string
	 */
	public function getSql()
	{
		$sql = "SELECT organization.id FROM organizations ";

		$parts = $this->getSqlParts();
		$order_by = $this->getOrderByPart();


		#------------------------------
		# Add joins
		#------------------------------

		foreach ($parts['joins'] as $j) {
			if (is_array($j)) {
				$sql .= $j[1] . " ";
			} else {
				$sql .= "LEFT JOIN $j ON $j.organization_id = organizations.id ";
			}
		}

		if (is_array($order_by)) {
			list ($order_join, $order_by) = $order_by;

			$sql .= " $order_join ";
		}

		#------------------------------
		# Add wheres
		#------------------------------

		if ($parts['wheres']) {
			$sql .= "WHERE ";
			$sql .= implode(" AND ", $parts['wheres']);
		}

		$sql .= " GROUP BY organizations.id ";
		$sql .= $order_by;
		$sql .= " LIMIT 1000";

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
			$this->order_by = array('organizations.name', 'ASC');
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
			case 'organization.name':
				$order_by = "ORDER BY organizations.name $dir";
				break;

			case 'organization.organization_field':
				$field = App::getEntityRepository('DeskPRO:CustomDefOrganization')->find($term_id);
				if (!$field) break;

				$search_type = $field->getHandler()->getSearchType();

				switch ($search_type) {
					case 'input':
					case 'value':
						$order_by = arary(
							"INNER JOIN custom_data_organization AS sort_table ON (sort_table.organization_id = organizations.id AND sort_table.id = $term_id)",
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
		$people_table = 'organizations';

		$db = App::getDb();

		$wheres = array();
		$joins = array();

		foreach ($this->terms as $term => $info) {
			$join_id = Util::requestUniqueId();
			$join_name = "j_$join_id";

			list($op, $choice) = $info;

			$term_id = null;

			// $term of people_field[12] becomes $term=people_field, $term_id=12
			$m = null;
			if (preg_match('#^(.*?)\[(.*?)\]$#', $term, $m)) {
				$term = $m[1];
				$term_id = $m[2];
			}

			switch ($term) {
                case self::TERM_ID:
					$wheres[] = $this->_rangeMatch("$tickets_table.id", $op, $choice, true);
					$this->summary[] = $this->_rangeSummary($tr->phrase('core.id'), $op, $choice);
					break;
				case self::TERM_NAME:
					$wheres[] = $this->_stringMatch("organizations.name", $op, $choice);;

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

					switch ($op) {
						case self::OP_IS:
							$joins[] = array(
								'labels_people',
								"LEFT JOIN labels_people AS $join_name ON ($join_name.person_id = people.id)"
							);
							$wheres[] = "$join_name.label = " . $db->quote($choice);
							break;
						case self::OP_NOT:
							$joins[] = array(
								'labels_people',
								"LEFT JOIN labels_people AS $join_name ON ($join_name.person_id = people.id AND $join_name.label = '.$db->quote($choice).')"
							);
							$wheres[] = "$join_name.person_id IS NULL";
							break;
						case self::OP_CONTAINS:
							$joins[] = array(
								'labels_people',
								"LEFT JOIN labels_people AS $join_name ON ($join_name.person_id = people.id)"
							);
							$wheres[] = "$join_name.label IN ($choices_in)";
							break;

						case self::OP_NOTCONTAINS:
							$joins[] = array(
								'labels_people',
								"LEFT JOIN labels_people AS $join_name ON ($join_name.person_id = people.id AND $join_name.label IN ($choices_in)"
							);
							$wheres[] = "$join_name.person_id IS NULL";
							break;
					}
					break;

				case self::TERM_ORGANIZATION_FIELD:

					$field = App::getEntityRepository('DeskPRO:CustomDefOrganization')->find($term_id);
					if (!$field) break;

					$search_type = $field->getHandler()->getSearchType();

					switch ($search_type) {
						case 'input':
						case 'value':

							$join_id = Util::requestUniqueId();
							$joins[] = array(
								'custom_data_organization',
								"LEFT JOIN custom_data_organization AS $join_id ON ($join_id.person_id = organizations.id AND $join_id.field_id = $term_id)"
							);

							$field = $join_id.'.'.$search_type;
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

							$field = $join_id.'.field_id';
							switch ($op) {
								case self::OP_CONTAINS:
									$joins[] = array(
										'custom_data_organization',
										"LEFT JOIN custom_data_organization AS $join_id ON ($join_id.person_id = people.id)"
									);
									$wheres[] = "$field IN ($choices_in)";
									break;

								case self::OP_NOTCONTAINS:
									$joins[] = array(
										'custom_data_organization',
										"LEFT JOIN custom_data_organization AS $join_id ON ($join_id.person_id = people.id AND $join_id.field_id IN ($choices_in)"
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
}