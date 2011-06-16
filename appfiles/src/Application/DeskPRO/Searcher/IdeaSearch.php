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
	const TERM_CATEGORY        = 'category';
	const TERM_HIDDEN_STATUS   = 'hidden_status';
	const TERM_VOTES           = 'num_votes';
	const TERM_DATE_CREATED    = 'date_created';
	const TERM_POPULAR         = 'popular';

	const ORDER_DATE  = 'id';
	const ORDER_VOTES = 'num_votes';

	
	/**
	 * Run the search and return an array of matching ID's.
	 *
	 * @param int $limit
	 * @return array
	 */
	public function getMatches()
	{
		$db = App::getDb();

		$idea_ids = $db->fetchAllCol($this->getSql());

		return $idea_ids;
	}


	/**
	 * Get the SQL query that'll fetch the results
	 * @return string
	 */
	public function getSql()
	{
		$sql = "SELECT ideas.id FROM ideas ";

		$parts = $this->getSqlParts();
		$order_by = $this->getOrderByPart();


		#------------------------------
		# Add joins
		#------------------------------

		foreach ($parts['joins'] as $j) {
			$sql .= "LEFT JOIN $j ON $j.person_id = people.id ";
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
				$order_by = "ORDER BY ideas.id $dir";
				break;

			case 'num_votes':
				$order_by = "ORDER BY ideas.num_votes $dir";
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
					$wheres[] = $this->_stringMatch('ideas.hidden_status', $op, $choice);
					break;

				case self::TERM_STATUS:
					$choice = array_pop($choice);

					// A sub-status which are customizable (Active > Considering for example)
					if (ctype_digit($choice)) {
						$wheres[] = $this->_choiceMatch('ideas.status_category_id', $op, $choice);
						
					// A top level status (active, closed etc)
					} else {
						$wheres[] = $this->_stringMatch('ideas.status', $op, $choice);
					}

					break;

				case self::TERM_CATEGORY:
					$base_ids = is_array($choice['category']) ? $choice['category'] : array($choice['category']);
					$ids = array();

					foreach ($base_ids as $id) {
						$ids = array_merge($ids, App::getEntityRepository('DeskPRO:IdeaCategory')->getIdsInTree($id));
					}

					$ids = array_unique($ids);

					$wheres[] = $this->_choiceMatch('ideas.status_category_id', $op, $ids);
					break;

				case self::TERM_VOTES:
					$wheres[] = $this->_rangeMatch('ideas.num_votes', $op, $choice);
					break;

				case self::TERM_POPULAR:
					$wheres[] = $this->_rangeMatch('ideas.num_votes', $op, App::getSetting('core_ideas.popular_votes'));
					break;

				case self::TERM_DATE_CREATED:
					$wheres[] = $this->_dateMatch('idaes.date_created', $op, $choice);
					break;
			}
		}

		$joins = array_unique($joins);

		return array(
			'joins' => $joins,
			'wheres' => $wheres
		);
	}
}