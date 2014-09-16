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

use Application\DeskPRO\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;

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
		'articles'             => array('table' => 'labels_articles',           'entity' => 'DeskPRO:LabelArticle'),
		'deals'                => array('table' => 'labels_blobs',              'entity' => 'DeskPRO:LabelDeal'),
		'downloads'            => array('table' => 'labels_downloads',          'entity' => 'DeskPRO:LabelDownload'),
		'feedback'             => array('table' => 'labels_feedback',           'entity' => 'DeskPRO:LabelFeedback'),
		'chat'                 => array('table' => 'labels_chat_conversations', 'entity' => 'DeskPRO:LabelChatConversation'),
		'news'                 => array('table' => 'labels_news',               'entity' => 'DeskPRO:LabelNews'),
		'organizations'        => array('table' => 'labels_organizations',      'entity' => 'DeskPRO:LabelOrganization'),
		'people'               => array('table' => 'labels_people',             'entity' => 'DeskPRO:LabelPeople'),
		'tasks'                => array('table' => 'labels_tasks',              'entity' => 'DeskPRO:LabelTask'),
		'tickets'              => array('table' => 'labels_tickets',            'entity' => 'DeskPRO:LabelTicket'),
		'kb'                   => array('table' => 'labels_articles',           'entity' => 'DeskPRO:LabelArticle'),
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
	 * @return array
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
		static $valid = array('articles', 'downloads', 'feedback', 'news', 'organizations', 'people', 'tickets', 'chat_conversations');

		if ($types === null) {
			$types = $valid;
		}
		if (is_string($types)) {
			$types = explode(',', $types);
			$types = array_map('trim', $types);
		}

		if (!$types) {
			return array();
		}

		$types = array_map(function($t) {
			if ($t == 'chat') $t = 'chat_conversations';
			return $t;
		}, $types);

		// invalid type(s)
		if (array_diff($types, $valid)) {
			throw new \InvalidArgumentException();
		}

		$parts = array('SELECT DISTINCT(label) FROM label_defs ');
		$params = array();
		$qtypes = array();

		if (count($types) < 8) {
			$parts[0] .= ' WHERE label_type IN (?)';
			$params[] = $types;
			$qtypes[] = Connection::PARAM_STR_ARRAY;
		}

		foreach ($types as $t) {
			$parts[] = 'SELECT DISTINCT(label) FROM ' . $this->db->quoteIdentifier('labels_' . $t);
		}

		$q = '(' . implode(') UNION (', $parts) . ')';
		$labels = $this->db->fetchAllCol($q, $params, $qtypes);

		return $labels;
	}


	/**
	 * @return array
	 */
	public function getAllLabelsToTyped()
	{
		$ret = array();

		// Admin defined
		foreach ($this->db->fetchAll("SELECT * FROM label_defs") as $x) {
			if (!isset($x['label'])) {
				$ret[$x['label']] = array();
			}

			$ret[$x['label']][] = $x['label_type'];
		}

		// Non-admin defined
		$types = array('articles', 'downloads', 'feedback', 'news', 'organizations', 'people', 'tickets', 'chat_conversations');
		$parts = array();
		foreach ($types as $t) {
			$parts[] = "SELECT DISTINCT(label) AS label, '$t' AS label_type FROM labels_$t";
		}

		$q = '(' . implode(') UNION (', $parts) . ')';
		foreach ($this->db->fetchAll($q) as $x) {
			if (!isset($x['label'])) {
				$ret[$x['label']] = array();
			}

			$ret[$x['label']][] = $x['label_type'];
		}

		return $ret;
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
	 * @param array $types
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
		return true;
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
				$this->db->executeUpdate("DELETE FROM $table WHERE label = ?", array($old_label));
			}

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		#------------------------------
		# Rename labels within filters/macros/triggers
		#------------------------------

		$replace_label_arr = function($actions_str, $accept_types) use ($old_label, $new_label) {
			$actions = @unserialize($actions_str);

			if (!$actions) {
				return $actions_str;
			}

			foreach ($actions as &$a) {
				if (isset($a['type']) && in_array($a['type'], $accept_types) && !empty($a['options']['labels'])) {
					$a['options']['labels'] = Arrays::replaceValue($a['options']['labels'], $old_label, $new_label);
					$a['options']['labels'] = array_unique($a['options']['labels']);
				}
			}
			unset($a);

			$actions_str = serialize($actions);
			return $actions_str;
		};

		foreach ($types as $t) {
			if ($t == 'tickets') {
				$macros = $this->db->fetchAll("
					SELECT id, actions
					FROM ticket_macros
					WHERE actions LIKE '%\"add_labels\"%' OR actions LIKE '%\"remove_labels\"%'
				");
				foreach ($macros as $r) {
					$actions_new = $replace_label_arr($r['actions'], array('add_labels', 'remove_labels'));

					if ($actions_new != $r['actions']) {
						$this->db->update('ticket_macros', array('actions' => $actions_new), array('id' => $r['id']));
					}
				}
			}

			// TODO - fix removing labels from triggers/filters
			if (false && in_array($t, array('persons', 'tickets', 'organizations'))) {
				$triggers = $this->db->fetchAll("
					SELECT id, actions, terms, terms_any
					FROM ticket_triggers
					WHERE
						actions LIKE '%\"add_labels\"%'
						OR actions LIKE '%\"remove_labels\"%'
						OR terms LIKE '%\"label\"%'
						OR terms LIKE '%\"org_label\"%'
						OR terms LIKE '%\"person_label\"%'
						OR terms_any LIKE '%\"label\"%'
						OR terms_any LIKE '%\"person_label\"%'
						OR terms_any LIKE '%\"org_label\"%'
				");
				foreach ($triggers as $r) {
					$changes = array();
					if ($t == 'tickets') {
						$actions_new = $replace_label_arr($r['actions'], array('add_labels', 'remove_labels'));
						if ($actions_new != $r['actions']) {
							$changes['actions'] = $actions_new;
						}
					}

					$terms_new = $r['terms'];
					if ($t == 'tickets') $terms_new = $replace_label_arr($terms_new, array('ticket_label', 'label'));
					if ($t == 'persons') $terms_new = $replace_label_arr($terms_new, array('person_label'));
					if ($t == 'organizations') $terms_new = $replace_label_arr($terms_new, array('org_label'));
					if ($terms_new != $r['terms']) {
						$changes['terms'] = $terms_new;
					}

					$terms_any_new = $r['terms_any'];
					if ($t == 'tickets') $terms_new = $replace_label_arr($terms_any_new, array('ticket_label', 'label'));
					if ($t == 'persons') $terms_new = $replace_label_arr($terms_any_new, array('person_label'));
					if ($t == 'organizations') $terms_new = $replace_label_arr($terms_any_new, array('org_label'));
					if ($terms_any_new != $r['terms']) {
						$changes['terms_any'] = $terms_any_new;
					}

					if ($changes) {
						$this->db->update('ticket_triggers', $changes, array('id' => $r['id']));
					}
				}

				$filters = $this->db->fetchAll("
					SELECT id, terms
					FROM ticket_filters
					WHERE
						terms LIKE '%\"label\"%'
						OR terms LIKE '%\"org_label\"%'
						OR terms LIKE '%\"person_label\"%'
				");
				foreach ($filters as $r) {

					$changes = array();
					$terms_new = $r['terms'];
					if ($t == 'tickets') $terms_new = $replace_label_arr($terms_new, array('ticket_label', 'label'));
					if ($t == 'persons') $terms_new = $replace_label_arr($terms_new, array('person_label'));
					if ($t == 'organizations') $terms_new = $replace_label_arr($terms_new, array('org_label'));
					if ($terms_new != $r['terms']) {
						$changes['terms'] = $terms_new;
					}

					if ($changes) {
						$this->db->update('ticket_filters', $changes, array('id' => $r['id']));
					}
				}
			}
		}
	}
}
