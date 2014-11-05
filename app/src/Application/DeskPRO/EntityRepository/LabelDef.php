<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
use Doctrine\DBAL\Connection;
use Application\DeskPRO\Entity;
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
				$label_type = 'feedback';
				break;

			case 'news':
				$label_type = 'news';
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
		$conn = $this->getEntityManager()->getConnection();
		$sql = '
			SELECT label, total
			FROM label_defs
			WHERE label_type = ?
			ORDER BY total DESC
		';
		$params = array($label_type);
		$types = array(\PDO::PARAM_STR);

		if ($limit) {
			$sql .= ' LIMIT ?';
			$params[] = $limit;
			$types[] = \PDO::PARAM_INT;
		}

		return $conn->fetchAllKeyValue($sql, $params, $types);
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
			case 'people':
				return 'DeskPRO:LabelPerson';
			case 'tickets':
				return 'DeskPRO:LabelTicket';
			case 'articles':
				return 'DeskPRO:LabelArticle';
			case 'feedback':
				return 'DeskPRO:LabelFeedback';
			case 'downloads':
				return 'DeskPRO:LabelDownload';
			case 'news':
				return 'DeskPRO:LabelNews';
			case 'chat':
				return 'DeskPRO:LabelChatConversation';
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
			case 'chat':
				return 'labels_chat_conversations';
		}

		return null;
	}

	public function getTypeByEntityName($entityName)
	{
		if (false === $type = array_search($entityName, $this->getLabelEntities(), 1)) {
			return null;
		}

		$t = substr($type, 7);

		if ($t == 'chat_conversations') {
			return 'chat';
		}

		return $t;
	}

	public function findLabelsByType($type)
	{
		$db = $this->getEntityManager()->getConnection();

		$ret = array();

		$table = $this->getLabelTableFromType($type);
		if ($table) {
			$res = $db->executeQuery("
				SELECT DISTINCT(label)
				FROM $table
			");
			while ($row = $res->fetchColumn(0)) {
				$ret[strtolower($row)] = $row;
			}
		}

		$res = $db->executeQuery(sprintf(
			'SELECT label FROM %s WHERE label_type = :type', $this->getTableName()
		), array('type' => $type));

		while ($row = $res->fetchColumn(0)) {
			$ret[strtolower($row)] = $row;
		}

		return array_values($ret);
	}

	public function correctLabels($type, array $labels, $allow_new = true)
	{
		if (!$labels) {
			return array();
		}
		$db = $this->getEntityManager()->getConnection();

		$ret = array();

		$table = $this->getLabelTableFromType($type);
		if ($table) {
			$res = $db->executeQuery("
				SELECT DISTINCT(label)
				FROM $table
				WHERE label IN (?)
			", array($labels), array(Connection::PARAM_STR_ARRAY));
			while ($row = $res->fetchColumn(0)) {
				$ret[strtolower($row)] = $row;
			}
		}

		$res = $db->executeQuery(sprintf('
			SELECT label FROM %s WHERE label_type = ? AND label IN (?)
		', $this->getTableName()), array($type, $labels), array(\PDO::PARAM_STR, Connection::PARAM_STR_ARRAY));

		while ($row = $res->fetchColumn(0)) {
			$ret[strtolower($row)] = $row;
		}

		if ($allow_new) {
			foreach ($labels as $l) {
				$ll = strtolower($l);
				if (!isset($ret[$ll])) {
					$ret[$ll] = $l;
				}
			}
		}

		return array_values($ret);
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
			'labels_chat_conversations' => 'DeskPRO:LabelChatConversation',
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
			'SELECT d FROM DeskPRO:LabelDef d WHERE d.label_type = :type AND d.label = :label'
		)->setParameters(array(
			'type' => $type,
			'label' => trim($label),
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
			$label = $def['label'];
			$def['total'] = isset($counts[$def['label_type']][$label]) ? $counts[$def['label_type']][$label] : 0;
		}

		return $definitions;
	}

	/**
	 * @param \Application\DeskPRO\Entity\LabelDef $definition
	 */
	public function updateDefinitionUsages(\Application\DeskPRO\Entity\LabelDef $definition)
	{
		// Need to run an update to change cases because table is case-insensitive
		$this->_em->getConnection()->executeUpdate("
			UPDATE IGNORE label_defs SET label = ? WHERE label = ?
		", array($definition->label, $definition->label));

		$this->_em->getConnection()->executeUpdate(sprintf("
			UPDATE IGNORE %s SET label = ? WHERE label = ?
		", self::$types[$definition['label_type']]['table']), array($definition->label, $definition->label));

		$counts = $this->countDefUsages(array($definition['label_type']));
		$label = $definition['label'];
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
				sprintf('DELETE FROM %s WHERE label = ?', self::$types[$definition['label_type']]['table']),
				array($definition['label'])
			);
			$this->getEntityManager()->remove($definition);
			$this->getEntityManager()->flush();

			$this->getEntityManager()->getConnection()->commit();
		} catch (\Exception $e) {
			$this->getEntityManager()->getConnection()->rollback();
			throw $e;
		}
	}

	public function updateColorForLabel($type, $label, $color)
	{
		$this->getEntityManager()->createQuery('
			UPDATE DeskPRO:LabelDef l
			SET l.color = :color
			WHERE l.label = :label AND l.label_type = :label_type
		')->execute(array('label_type' => $type, 'label' => $label, 'color' => $color));
	}

	public function getColorForLabel($label)
	{
		$q = $this->getEntityManager()->createQuery('
			SELECT d.color FROM DeskPRO:LabelDef d WHERE d.label = :label
		')->setMaxResults(1)->setParameters(array('label' => $label));

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
			$query .= 'SELECT "'.$t.'" as label_type, COUNT(*) AS count, label as label FROM ' . $info['table'] . ' GROUP BY label';
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
	 * @param string $old_label
	 * @param string $new_label
	 * @param string $color
	 * @param string $type
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 * @throws \Exception
	 */
	public function renameLabelDef($old_label, $new_label, $color, $type)
	{
		if (!self::valid($type)) {
			throw new NotFoundHttpException;
		}

		$types = (array)$type;

		$this->getEntityManager()->getConnection()->beginTransaction();

		try {
			foreach ($types as $t) {
				$table = self::$types[$t]['table'];

				$def_new = $this->getDefinition($t, $new_label);
				$def_old = $this->getDefinition($t, $old_label);

				$this->getEntityManager()->getConnection()->executeUpdate(
					'UPDATE IGNORE ' . $table . ' SET label = ? WHERE label = ?',
					array($new_label, $old_label)
				);

				// Same one -- we are just changing case
				if ($def_new && $def_old && $def_old === $def_old) {
					$def_new->label = $new_label;
				} else {
					$this->getEntityManager()->getConnection()->executeUpdate(
						'DELETE FROM ' . $table . ' WHERE label = ?',
						array($old_label)
					);
				}

				if ($def_old && $def_new) {
					$this->getEntityManager()->remove($def_old);
				} else if ($def_old && !$def_new || !$def_old && !$def_new) {
					$def_new = new Entity\LabelDef();
					$def_new->label_type = $type;
					$def_new->color = $color ?: '';
					$def_new->label = $new_label;
				} else if (!$def_old && $def_new) {
					// nothing to do
				}

				$def_new->total = $this->getEntityManager()->getConnection()->fetchColumn("
					SELECT COUNT(*)
					FROM $table
					WHERE label = ?
				", array($new_label));
				$this->getEntityManager()->persist($def_new);
			}

			$this->getEntityManager()->flush();
			$this->getEntityManager()->getConnection()->commit();
		} catch (\Exception $e) {
			$this->getEntityManager()->getConnection()->rollback();
			throw $e;
		}
	}

	static public function valid($type = null)
	{
		return null === $type ? array_keys(self::$types) : isset(self::$types[$type]);
	}
}
