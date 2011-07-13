<?php

namespace Application\DeskPRO\Searcher;

use Application\DeskPRO\App;

use Orb\Util\Util;
use Orb\Util\Strings;
use Orb\Util\Arrays;

class ArticleSearch extends SearcherAbstract
{
	const TERM_ID              = 'id';
	const TERM_STATUS          = 'status';
	const TERM_HIDDEN_STATUS   = 'hidden_status';
	const TERM_CATEGORY        = 'category';
	const TERM_DATE_CREATED    = 'date_created';
	const TERM_VIEW_COUNT      = 'view_count';
	const TERM_LABEL           = 'label';

	const ORDER_ID    = 'id';
	const ORDER_DATE  = 'id';
	const ORDER_VIEWS = 'view_count';

	/**
	 * Run the search and return an array of matching ID's.
	 *
	 * @param array $limit
	 * @return array
	 */
	public function getMatches(array $limit = null)
	{
		$db = App::getDb();

		$article_ids = $db->fetchAllCol($this->getSql($limit));

		return $article_ids;
	}


	/**
	 * Get the total number of matches
	 *
	 * @return int
	 */
	public function getCount()
	{
		$sql = "SELECT COUNT(*) FROM articles ";
		$parts = $this->getSqlParts();
		$order_by = $this->getOrderByPart();

		#------------------------------
		# Add joins
		#------------------------------

		foreach ($parts['joins'] as $j) {
			if (is_array($j)) {
				$sql .= $j[1] . " ";
			} else {
				$sql .= "LEFT JOIN $j ON $j.article_id = articles.id ";
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
		$sql = "SELECT articles.id FROM articles ";

		$parts = $this->getSqlParts();
		$order_by = $this->getOrderByPart();


		#------------------------------
		# Add joins
		#------------------------------

		foreach ($parts['joins'] as $j) {
			if (is_array($j)) {
				$sql .= $j[1] . " ";
			} else {
				$sql .= "LEFT JOIN $j ON $j.article_id = articles.id ";
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

		$sql .= " GROUP BY articles.id ";
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
			case 'date':
				$order_by = "ORDER BY articles.id $dir";
				break;

			case 'view_count':
				$order_by = "ORDER BY articles.view_count $dir";
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
					$wheres[] = $this->_rangeMatch("articles.id", $op, $choice, true);
					break;

				case self::TERM_HIDDEN_STATUS:
					$wheres[] = $this->_stringMatch('articles.hidden_status', $op, $choice);
					break;

				case self::TERM_STATUS:
					$wheres[] = $this->_stringMatch('articles.status', $op, $choice);
					break;

				case self::TERM_CATEGORY:
					$base_ids = is_array($choice['category']) ? $choice['category'] : array($choice);
					$ids = array();

					foreach ($base_ids as $id) {
						$ids = array_merge($ids, App::getEntityRepository('DeskPRO:ArticleCategory')->getIdsInTree($id, true));
					}

					$ids = array_unique($ids);

					$joins[] = array(
						'article_to_categories',
						"LEFT JOIN article_to_categories AS $join_name ON ($join_name.article_id = articles.id)"
					);

					$wheres[] = $this->_choiceMatch("$join_name.category_id", $op, $ids);
					break;

				case self::TERM_VIEW_COUNT:
					$wheres[] = $this->_rangeMatch('articles.view_count', $op, $choice);
					break;

				case self::TERM_DATE_CREATED:
					$wheres[] = $this->_dateMatch('articles.date_created', $op, $choice);
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
								'labels_articles',
								"LEFT JOIN labels_articles AS $join_name ON ($join_name.article_id = articles.id)"
							);
							$wheres[] = "$join_name.label = " . $db->quote($choice);
							break;
						case self::OP_NOT:
							$joins[] = array(
								'labels_articles',
								"LEFT JOIN labels_articles AS $join_name ON ($join_name.article_id = articles.id AND $join_name.label = '.$db->quote($choice).')"
							);
							$wheres[] = "$join_name.person_id IS NULL";
							break;
						case self::OP_CONTAINS:
							$joins[] = array(
								'labels_articles',
								"LEFT JOIN labels_articles AS $join_name ON ($join_name.article_id = articles.id)"
							);
							$wheres[] = "$join_name.label IN ($choices_in)";
							break;

						case self::OP_NOTCONTAINS:
							$joins[] = array(
								'labels_articles',
								"LEFT JOIN labels_articles AS $join_name ON ($join_name.article_id = articles.id AND $join_name.label IN ($choices_in)"
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