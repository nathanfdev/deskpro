<?php

namespace Application\DeskPRO\Searcher;

use Application\DeskPRO\App;

use Orb\Util\Util;
use Orb\Util\Strings;
use Orb\Util\Arrays;

class IdeaSearch extends SearcherAbstract
{
	const TERM_ID              = 'id';
	const TERM_STATUS          = 'status';
	const TERM_HIDDEN_STATUS   = 'hidden_status';
	const TERM_CATEGORY        = 'category';
	const TERM_CATEGORY_SPECIFIC = 'category_specific';
	const TERM_NUM_RATINGS       = 'num_ratings';
	const TERM_DATE_CREATED    = 'date_created';
	const TERM_LABEL           = 'label';

	const ORDER_ID    = 'id';
	const ORDER_DATE  = 'id';
	const ORDER_NUM_RATINGS = 'num_ratings';


	/**
	 * Run the search and return an array of matching ID's.
	 *
	 * @param int $limit
	 * @return array
	 */
	public function getMatches(array $limit = null)
	{
		$db = App::getDb();

		$idea_ids = $db->fetchAllCol($this->getSql($limit));

		return $idea_ids;
	}


	/**
	 * Get actual model objects for matches
	 *
	 * @param array $limit
	 * @return array
	 */
	public function getMatchingObjects(array $limit = null)
	{
		$ids = $this->getMatches($limit);

		if (!$ids) return array();

		return App::getEntityRepository('DeskPRO:Idea')->getByResultIds($ids);
	}


	/**
	 * Get the total number of matches
	 *
	 * @return int
	 */
	public function getCount()
	{
		$sql = "SELECT COUNT(*) FROM ideas ";
		$parts = $this->getSqlParts();
		$order_by = $this->getOrderByPart();

		#------------------------------
		# Add joins
		#------------------------------

		foreach ($parts['joins'] as $j) {
			if (is_array($j)) {
				$sql .= $j[1] . " ";
			} else {
				$sql .= "LEFT JOIN $j ON $j.idea_id = ideas.id ";
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

		$count = App::getDb()->fetchColumn($sql);

		return $count;
	}


	/**
	 * Get the SQL query that'll fetch the results
	 *
	 * @return string
	 */
	public function getSql(array $limit = null)
	{
		$sql = "SELECT ideas.id FROM ideas ";

		$parts = $this->getSqlParts();
		$order_by = $this->getOrderByPart();


		#------------------------------
		# Add joins
		#------------------------------

		foreach ($parts['joins'] as $j) {
			if (is_array($j)) {
				$sql .= $j[1] . " ";
			} else {
				$sql .= "LEFT JOIN $j ON $j.idea_id = ideas.id ";
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

		$sql .= " GROUP BY ideas.id ";
		$sql .= $order_by;

		if ($limit) {
			$sql .= " LIMIT {$limit['offset']},{$limit['max']}";
		} else {
			$sql .= " LIMIT 1000";
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
			$this->order_by = array('id', 'DESC');
		}

		list($type, $dir) = $this->order_by;

		$dir = strtoupper($dir);
		if ($dir != self::ORDER_ASC AND $dir != self::ORDER_DESC) {
			$dir = self::ORDER_DESC;
		}

		$order_by = '';

		switch ($type) {
			case 'id':
			case 'date_created':
				$order_by = "ORDER BY ideas.id $dir";
				break;

			//case 'popularity':
			//	$order_by = "ORDER BY ideas.popularity $dir";
			//	break;

			case 'num_ratings':
				$order_by = "ORDER BY ideas.num_ratings $dir";
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
		$db = App::getDb();

		$wheres = array();
		$joins = array();

		foreach ($this->terms as $term => $info) {
			$join_id = Util::requestUniqueId();
			$join_name = "j_$join_id";

			list($op, $choice) = $info;
			$term_id = null;

			switch ($term) {
                case self::TERM_ID:
					$wheres[] = $this->_rangeMatch("ideas.id", $op, $choice, true);
					break;

				case self::TERM_HIDDEN_STATUS:
					if ($op == 'not') {
						$wheres[] = '(ideas.hidden_status IS NULL OR ' . $this->_stringMatch('ideas.hidden_status', $op, $choice) . ')';
					} else {
						$wheres[] = $this->_stringMatch('ideas.hidden_status', $op, $choice);
					}
					break;

				case self::TERM_STATUS:

					$cats = array();
					$types = array();

					foreach ((array)$choice as $c) {
						if (strpos($c, '.') !== false) {
							list (, $c) = explode('.', $c, 2);
						}
						if (ctype_digit($c)) {
							$cats[] = $c;
						} else {
							$types[] = $c;
						}
					}

					// Visible is a special type name
					if (($k = array_search('visible', $types)) !== false) {
						unset($types[$k]);
						$types = array_merge($types, array('new', 'active', 'closed'));
						$types = array_unique($types);
					}

					$part_where = array();
					if ($cats) {
						$part_where[] = $this->_choiceMatch('ideas.status_category_id', $op, $cats);
					}
					if ($types) {
						$part_where[] = $this->_stringMatch('ideas.status', $op, $types);
					}

					$part_where = "(" . implode(' OR ', $part_where) . ")";
					$wheres[] = $part_where;

					break;

				case self::TERM_CATEGORY:
				case self::TERM_CATEGORY_SPECIFIC:
					$base_ids = (array)(is_array($choice['category']) ? $choice['category'] : $choice);
					$ids = array();

					if ($term == self::TERM_CATEGORY_SPECIFIC) {
						$ids = $base_ids;
					} else {
						foreach ($base_ids as $id) {
							$ids = array_merge($ids, App::getEntityRepository('DeskPRO:IdeaCategory')->getIdsInTree($id, true));
						}
					}

					$ids = array_unique($ids);

					$wheres[] = $this->_choiceMatch('ideas.category_id', $op, $ids);

					$this->summary[] = $this->_choiceSummary('Category', $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:IdeaCategory')->getCategoryNames((array)$choice);
						return $titles;
					});
					break;

				case self::TERM_NUM_RATINGS:
					$wheres[] = $this->_rangeMatch('ideas.num_ratings', $op, $choice);
					break;

				case self::TERM_DATE_CREATED:
					$wheres[] = $this->_dateMatch('idaes.date_created', $op, $choice);
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
								'labels_ideas',
								"LEFT JOIN labels_ideas AS $join_name ON ($join_name.idea_id = ideas.id)"
							);
							$wheres[] = "$join_name.label = " . $db->quote($choice);
							break;
						case self::OP_NOT:
							$joins[] = array(
								'labels_ideas',
								"LEFT JOIN labels_ideas AS $join_name ON ($join_name.idea_id = ideas.id AND $join_name.label = '.$db->quote($choice).')"
							);
							$wheres[] = "$join_name.person_id IS NULL";
							break;
						case self::OP_CONTAINS:
							$joins[] = array(
								'labels_ideas',
								"LEFT JOIN labels_ideas AS $join_name ON ($join_name.idea_id = ideas.id)"
							);
							$wheres[] = "$join_name.label IN ($choices_in)";
							break;

						case self::OP_NOTCONTAINS:
							$joins[] = array(
								'labels_ideas',
								"LEFT JOIN labels_ideas AS $join_name ON ($join_name.idea_id = ideas.id AND $join_name.label IN ($choices_in)"
							);
							$wheres[] = "$join_name.person_id IS NULL";
							break;
					}
					break;// end labels
			}
		}

		$joins = array_unique($joins);

		return array(
			'joins' => $joins,
			'wheres' => $wheres
		);
	}
}
