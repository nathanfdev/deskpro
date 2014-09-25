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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LabelDef extends AbstractEntityRepository
{
	static protected $types = array(
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
	 * Get the top counts for labels of a certain type.
	 *
	 * @return array
	 */
	public function getLabelCounts($type, $limit = 25)
	{
		switch ($type) {
			case 'tickets':
			case 'ticket':
				$label_type = 'tickets';
				break;

			case 'people':
				$label_type = 'people';
				break;

			case 'feedback':
				$label_type = 'feedbacks';
				break;

			case 'news':
				$label_type = 'newss';
				break;

			case 'organizations':
			case 'chat_conversations':
			case 'articles':
			case 'downloads':
				$label_type = $type;
				break;

			default:
				throw new \InvalidArgumentException("`$type` is an invalid label type");
				break;
		}

		return $this->getEntityManager()->getConnection()->fetchAllKeyValue("
			SELECT label, total
			FROM label_defs
			WHERE label_type = ?
			ORDER BY total DESC
			" . ($limit ? "LIMIT $limit" : '') . "
		", array($label_type));
	}

	/**
	 * Get the name of the label entity given a type.
	 *
	 * @static
	 * @param  $label_type
	 * @return null|string
	 */
	public function getLabelEntityFromType($label_type)
	{
		switch ($label_type) {
			case 'organizations':
				return 'DeskPRO:LabelOrganization';
				break;

			case 'people':
				return 'DeskPRO:LabelPerson';
				break;

			case 'tickets':
				return 'DeskPRO:LabelTicket';
				break;

			case 'articles':
				return 'DeskPRO:LabelArticle';
				break;

			case 'feedback':
				return 'DeskPRO:LabelFeedback';
				break;

			case 'downloads':
				return 'DeskPRO:LabelDownload';
				break;

			case 'news':
				return 'DeskPRO:LabelNews';
				break;
		}

		return null;
	}


	/**
	 * @param string $label_type
	 * @return string|null
	 */
	public function getLabelTableFromType($label_type)
	{
		switch ($label_type) {
			case 'organizations':
				return 'labels_organizations';
			case 'people':
				return 'labels_people';
			case 'tickets':
				return 'labels_tickets';
			case 'articles':
				return 'labels_articles';
			case 'feedback':
				return 'labels_feedback';
			case 'downloads':
				return 'labels_downloads';
			case 'news':
				return 'labels_news';
		}

		return null;
	}

	public function getTypeByEntityName($entityName)
	{
		if (false === $type = array_search($entityName, $this->getLabelEntities(), 1)) {
			return null;
		}

		return substr($type, 7);
	}

	public function findLabelsByType($type)
	{
		$db = $this->getEntityManager()->getConnection();

		$ret = array();
		$res = $db->executeQuery(sprintf(
			'SELECT LOWER(label) as label FROM %s WHERE label_type = :type', $this->getTableName()
		), array('type' => $type));

		while ($row = $res->fetchColumn(0)) {
			$ret[] = $row;
		}

		$table = $this->getLabelTableFromType($type);
		if ($table) {
			$res = $db->executeQuery("
				SELECT DISTINCT(label)
				FROM $table
			");
			while ($row = $res->fetchColumn(0)) {
				$ret[] = $row;
			}
			$ret = array_unique($ret);
		}

		return $ret;
	}


	public function findLabelsByEntityName($entityName)
	{
		return $this->findLabelsByType($this->getTypeByEntityName($entityName));
	}

	/**
	 * A tablename=>entityname array of objects that have label capabiltiies.
	 *
	 * @static
	 * @return array
	 */
	public function getLabelEntities()
	{
		return array(
			'labels_organizations' => 'DeskPRO:LabelOrganization',
			'labels_people'        => 'DeskPRO:LabelPerson',
			'labels_tickets'       => 'DeskPRO:LabelTicket',
			'labels_articles'      => 'DeskPRO:LabelArticle',
			'labels_feedback'      => 'DeskPRO:LabelFeedback',
			'labels_downloads'     => 'DeskPRO:LabelDownload',
			'labels_news'          => 'DeskPRO:LabelNews',
		);
	}

	/**
	 * @param $type
	 * @param $label
	 * @return mixed
	 */
	public function getDefinition($type, $label)
	{
		return $this->getEntityManager()->createQuery(
			'SELECT d FROM DeskPRO:LabelDef d WHERE d.label_type = :type AND LOWER(d.label) = :label'
		)->setParameters(array(
			'type' => $type,
			'label' => strtolower(trim($label)),
		))->getOneOrNullResult();
	}

	/**
	 * @return array
	 */
	public function getAllDefinitions()
	{
		$definitions = $this->getEntityManager()->getConnection()->fetchAll('SELECT * FROM label_defs');
		$counts = $this->countDefUsages();

		foreach ($definitions as &$def) {
			$label = strtolower($def['label']);
			$def['total'] = isset($counts[$def['label_type']][$label]) ? $counts[$def['label_type']][$label] : 0;
		}

		return $definitions;
	}

	/**
	 * @param \Application\DeskPRO\Entity\LabelDef $definition
	 */
	public function updateDefinitionUsages(\Application\DeskPRO\Entity\LabelDef $definition)
	{
		$counts = $this->countDefUsages(array($definition['label_type']));
		$label = strtolower($definition['label']);
		$definition['total'] = isset($counts[$definition['label_type']][$label])
			? $counts[$definition['label_type']][$label]
			: 0;
	}

	/**
	 * @param \Application\DeskPRO\Entity\LabelDef $definition
	 * @throws \Exception
	 */
	public function deleteDefinition(\Application\DeskPRO\Entity\LabelDef $definition)
	{
		$this->getEntityManager()->getConnection()->beginTransaction();

		try {
			$this->getEntityManager()->getConnection()->executeUpdate(
				sprintf('DELETE FROM %s WHERE LOWER(label) = ?', self::$types[$definition['label_type']]['table']),
				array(strtolower($definition['label']))
			);
			$this->getEntityManager()->remove($definition);
			$this->getEntityManager()->flush();

			$this->getEntityManager()->getConnection()->commit();
		} catch (\Exception $e) {
			$this->getEntityManager()->getConnection()->rollback();
			throw $e;
		}
	}

	public function updateColorForLabel($label, $color)
	{
		$this->getEntityManager()->createQuery('
			UPDATE DeskPRO:LabelDef l SET l.color = :color WHERE LOWER(l.label) = :label
		')->execute(array('label' => strtolower($label), 'color' => $color));
	}

	public function getColorForLabel($label)
	{
		$q = $this->getEntityManager()->createQuery('
			SELECT d.color FROM DeskPRO:LabelDef d WHERE LOWER(d.label) = :label
		')->setMaxResults(1)->setParameters(array('label' => strtolower($label)));

		$res = $q->getScalarResult();

		return $res ? $res[0]['color'] : '#d4d4d4';
	}




	/***************** these are moved from LabelDefManager ****************/
	/** todo cleanup! */

	/**
	 * Get counts for all labels used for a type
	 * @param array $types
	 * @return mixed
	 */
	public function countDefUsages(array $types = array())
	{
		$query = '';
		$types = $types ?: array_keys(self::$types);

		foreach ($types as $k => $t) {
			$info = self::$types[$t];
			if ($k > 0) {
				$query .= "\n UNION ";
			}
			$query .= 'SELECT "'.$t.'" as label_type, COUNT(*) AS count, LOWER(label) as label FROM ' . $info['table'] . ' GROUP BY label';
		}

		$count_res = $this->getEntityManager()->getConnection()->fetchAll($query);

		$label_counts = array();
		foreach ($count_res as $r) {
			if (!isset($label_counts[$r['label_type']][$r['label']])) {
				$label_counts[$r['label_type']][$r['label']] = 0;
			}
			$label_counts[$r['label_type']][$r['label']] += $r['count'];
		}

		return $label_counts;
	}

	/**
	 * @return array
	 */
	public function getAllLabelsToTyped()
	{
		$ret = array();

		// Admin defined
		foreach ($this->getEntityManager()->getConnection()->fetchAll("SELECT * FROM label_defs") as $x) {
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
		foreach ($this->getEntityManager()->getConnection()->fetchAll($q) as $x) {
			if (!isset($x['label'])) {
				$ret[$x['label']] = array();
			}

			$ret[$x['label']][] = $x['label_type'];
		}

		return $ret;
	}

	/**
	 * TODO!
	 * Rename a label
	 *
	 * @param $old_label
	 * @param $new_label
	 * @param $color
	 * @param null $types
	 * @throws \Exception
	 */
	public function renameLabelDef($old_label, $new_label, $color, $type)
	{
		if (!self::valid($type)) {
			throw new NotFoundHttpException;
		}

		$types = (array)$type;

		$this->getEntityManager()->getConnection()->beginTransaction();

		if (!$definition = $this->getDefinition($type, $old_label)) {
			throw new NotFoundHttpException;
		}

		if ($exist = $this->getDefinition($type, $new_label)) {
			if ($definition['label'] !== $exist['label']) {
				$this->getEntityManager()->remove($exist);
				$this->getEntityManager()->flush();
			}
		}

		try {
			foreach ($types as $t) {
				$table = self::$types[$t]['table'];

				$this->getEntityManager()->getConnection()->executeUpdate(
					'UPDATE IGNORE ' . $table . ' SET label = ? WHERE LOWER(label) = ?',
					array($new_label, strtolower($old_label))
				);
			}

			$definition['label'] = $new_label;
			$definition['color'] = $color;
			$this->updateDefinitionUsages($definition);
			$this->getEntityManager()->flush();

			$this->getEntityManager()->getConnection()->commit();
		} catch (\Exception $e) {
			$this->getEntityManager()->getConnection()->rollback();
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
				$macros = $this->getEntityManager()->getConnection()->fetchAll("
					SELECT id, actions
					FROM ticket_macros
					WHERE actions LIKE '%\"add_labels\"%' OR actions LIKE '%\"remove_labels\"%'
				");
				foreach ($macros as $r) {
					$actions_new = $replace_label_arr($r['actions'], array('add_labels', 'remove_labels'));

					if ($actions_new != $r['actions']) {
						$this->getEntityManager()->getConnection()->update('ticket_macros', array('actions' => $actions_new), array('id' => $r['id']));
					}
				}
			}

			// TODO - fix removing labels from triggers/filters
			if (false && in_array($t, array('persons', 'tickets', 'organizations'))) {
				$triggers = $this->getEntityManager()->getConnection()->fetchAll("
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
						$this->getEntityManager()->getConnection()->update('ticket_triggers', $changes, array('id' => $r['id']));
					}
				}

				$filters = $this->getEntityManager()->getConnection()->fetchAll("
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
						$this->getEntityManager()->getConnection()->update('ticket_filters', $changes, array('id' => $r['id']));
					}
				}
			}
		}
	}

	static public function valid($type = null)
	{
		return null === $type ? array_keys(self::$types) : isset(self::$types[$type]);
	}
}
