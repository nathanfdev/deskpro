<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @category ORM
 */

namespace Application\DeskPRO\Labels;

use Doctrine\ORM\EntityManager;

class LabelDefManager
{
	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	protected $types = array(
		'articles'             => array('table' => 'labels_articles',       'entity' => 'DeskPRO:LabelArticle'),
		'deals'                => array('table' => 'labels_blobs',          'entity' => 'DeskPRO:LabelDeal'),
		'downloads'            => array('table' => 'labels_downloads',      'entity' => 'DeskPRO:LabelDownload'),
		'feedback'                => array('table' => 'labels_feedback',          'entity' => 'DeskPRO:LabelFeedback'),
		'news'                 => array('table' => 'labels_news',           'entity' => 'DeskPRO:LabelNews'),
		'organizations'        => array('table' => 'labels_organizations',  'entity' => 'DeskPRO:LabelOrganization'),
		'people'               => array('table' => 'labels_people',         'entity' => 'DeskPRO:LabelPeople'),
		'tasks'                => array('table' => 'labels_tasks',          'entity' => 'DeskPRO:LabelTask'),
		'tickets'              => array('table' => 'labels_tickets',        'entity' => 'DeskPRO:LabelTicket'),
	);

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->db = $em->getConnection();
	}


	/**
	 * Get an array of labels and their usage counts, ordered by $order_by
	 *
	 * @param mixed $types
	 * @param string $order_by
	 * @return string
	 */
	public function getLabelsAndCounts($types = null, $order_by = 'alpha')
	{
		$labels = $this->getLabels($types);
		$counts = $this->countDefUsages($types);

		$ret = array();

		foreach ($labels as $l) {
			$ret[$l] = 0;
			if (isset($counts[$l])) {
				$ret[$l] = $counts[$l];
			}
		}

		if ($order_by == 'count') {
			asort($ret, SORT_NUMERIC);
			$ret = array_reverse($ret, true);
		}

		return $ret;
	}


	/**
	 * Get defined labels
	 *
	 * @return array
	 */
	public function getLabels($types = null)
	{
		if ($types) {
			$labels = $this->db->fetchAllCol("SELECT DISTINCT label FROM label_defs WHERE label_type IN ('" . implode("','", (array)$types) . "') ORDER BY label ASC");
		} else {
			$labels = $this->db->fetchAllCol("SELECT DISTINCT label FROM label_defs ORDER BY label ASC");
		}

		return $labels;
	}


	/**
	 * Count all label definitions
	 *
	 * @return array
	 */
	public function countDefs()
	{
		$counts = $this->db->fetchAllKeyValue("
			SELECT label_type, COUNT(*) as count
			FROM label_defs
			GROUP BY label_type
		");

		$counts['TOTAL'] = $this->db->fetchColumn("
			SELECT COUNT(DISTINCT label) as count
			FROM label_defs
		");

		return $counts;
	}


	/**
	 * Get counts for all labels used for a type
	 *
	 * @param null $types
	 * @return array
	 */
	public function countDefUsages($types = null)
	{
		$query  = array();

		if (!$types) {
			$types = array_keys($this->types);
		} else {
			$types = (array)$types;
		}

		foreach ($types as $t) {
			$info = $this->types[$t];
			$query[]  = "SELECT COUNT(*) AS count, label FROM {$info['table']} GROUP BY label";
		}

		if (count($query) > 1) {
			$query = "(" . implode(") UNION (", $query) . ")";
		} else {
			$query = $query[0];
		}

		$count_res = $this->db->fetchAll($query);

		$label_counts = array();

		foreach ($count_res as $r) {
			if (!isset($label_counts[$r['label']])) $label_counts[$r['label']] = 0;
			$label_counts[$r['label']] += $r['count'];
		}

		return $label_counts;
	}


	/**
	 * Count usages of a label
	 *
	 * @param $label
	 * @param null $types
	 */
	public function countLabelUsages($label, $types = null)
	{
		$query  = array();
		$params = array();

		if (!$types) {
			$types = array_keys($this->types);
		} else {
			$types = (array)$types;
		}

		foreach ($types as $t) {
			$info = $this->types[$t];

			$query[]  = "SELECT COUNT(*) AS count FROM {$info['table']} WHERE label = ?";
			$params[] = $label;
		}

		if (count($query) > 1) {
			$query = "(" . implode(") UNION (", $query) . ")";
		} else {
			$query = $query[0];
		}

		$count_res = $this->db->fetchAll($query, $params);
		$count = 0;

		foreach ($count_res as $r) {
			$count += $r['count'];
		}

		return $count;
	}


	/**
	 * Create a new label definition
	 *
	 * @param $label
	 * @param null $types
	 */
	public function createLabelDef($label, $types = null)
	{
		if (!$types) {
			$types = array_keys($this->types);
		} else {
			$types = (array)$types;
		}

		$this->db->beginTransaction();

		try {
			foreach ($types as $t) {
				$this->db->executeUpdate("INSERT IGNORE INTO label_defs SET label_type = ?, label = ?, total = 0", array($t, $label));
			}

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}
	}


	/**
	 * Delete a label definition, and all its usages.
	 *
	 * @param $label
	 * @param null $types
	 */
	public function deleteLabelDef($label, $types = null)
	{
		if (!$types) {
			$types = array_keys($this->types);
		} else {
			$types = (array)$types;
		}

		$this->db->beginTransaction();

		try {
			foreach ($types as $t) {
				$table = $this->types[$t]['table'];

				$this->db->executeUpdate("DELETE FROM label_defs WHERE label_type = ? AND label = ?", array($t, $label));
				$this->db->executeUpdate("DELETE FROM $table WHERE label = ?", array($label));
			}

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}
	}


	/**
	 * Rename a label
	 *
	 * @param $old_label
	 * @param $new_label
	 * @param null $types
	 */
	public function renameLabelDef($old_label, $new_label, $types = null)
	{
		if (!$types) {
			$types = array_keys($this->types);
		} else {
			$types = (array)$types;
		}

		$this->db->beginTransaction();
		try {
			foreach ($types as $t) {
				$table = $this->types[$t]['table'];

				$this->db->executeUpdate("DELETE FROM label_defs WHERE label_type = ? AND label = ?", array($t, $old_label));
				$adjusted = $this->db->executeUpdate("UPDATE IGNORE $table SET label = ? WHERE label = ?", array($new_label, $old_label));
				$this->db->executeUpdate("
					INSERT INTO label_defs (label_type, label, total)
					VALUES (?, ?, ?)
					ON DUPLICATE KEY UPDATE total = total + VALUES(total)
				", array($t, $new_label, $adjusted));
				$this->db->executeUpdate("DELETE FROM $table WHERE label = ?", array($old_label));
			}

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}
	}
}
