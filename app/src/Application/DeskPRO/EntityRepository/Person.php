<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Organization as OrganizationEntity;
use Application\DeskPRO\Entity\Usergroup as UsergroupEntity;

use Orb\Util\Numbers;

class Person extends AbstractEntityRepository
{
	protected $_agent_names = null;

	protected function _loadAgentNames()
	{
		if ($this->_agent_names !== null) return;

		$db = App::getDb();
		$this->_agent_names = $db->fetchAllKeyValue("
			SELECT id, CONCAT_WS(' ', first_name, last_name) AS full_name
			FROM people
			WHERE is_agent = 1 AND is_deleted = 0 AND is_vacation_mode = 0
			ORDER BY full_name ASC
		");
	}

	public function findAgentByName($name)
	{
		try {
			$priority = $this->getEntityManager()->createQuery("
				SELECT p
				FROM DeskPRO:Person p
				WHERE CONCAT(first_name, ' ', last_name) LIKE ?1
			")->setParameter(1, "%$name%")->getSingleResult();
		} catch (\Exception $e) {
			return null;
		}

		return $priority;
	}

	public function getAgentNames($for_ids = null)
	{
		$this->_loadAgentNames();

		if ($for_ids === null) {
			return $this->_agent_names;
		}

		$ret = array();
		foreach ((array)$for_ids as $id) {
			if (isset($this->_agent_names[$id])) {
				$ret[$id] = $this->_agent_names[$id];
			}
		}

		return $ret;
	}


	/**
	 * Get all agents
	 *
	 * @return array
	 */
	public function getAgents()
	{
		return $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:Person p
			LEFT JOIN p.primary_email email
			LEFT JOIN p.picture_blob pic
			WHERE p.is_agent = true AND is_deleted = false AND is_vacation_mode = false
			ORDER BY p.name ASC
		")->execute();
	}


	/**
	 * Get all online and active (not away) agents.
	 *
	 * @param bool $ids_only
	 * @return array
	 */
	public function getActiveAgents($ids_only = false)
	{
		$cutoff = date('Y-m-d H:m:s', time() - App::getSetting('core.sessions_lifetime'));

		$sessions_q = App::getOrm()->createQuery("
			SELECT s,p
			FROM DeskPRO:Session s
			LEFT JOIN s.person p
			WHERE p.is_agent = true AND s.date_last > ?1
			GROUP BY p.id
			ORDER BY s.id DESC
		");

		$sessions = $sessions_q->setParameter(1, $cutoff)->execute();


		$online_agents = array();
		foreach ($sessions as $s) {
			if ($ids_only) {
				$online_agents[$s->person['id']] = $s->person['id'];
			} else {
				$online_agents[$s->person['id']] = $s->person;
			}
		}

		if ($ids_only) {
			$sessions_q->free();
		}

		return $online_agents;
	}

	/**
	 * Find a person by their email address.
	 *
	 * @param string $email
	 * @return Person
	 */
	public function findOneByEmail($email)
	{
		try {
			$person = $this->getEntityManager()->createQuery("
				SELECT p
				FROM DeskPRO:Person p
				LEFT JOIN p.emails e
				WHERE e.email = ?1
				ORDER BY p.id ASC
			")->setParameter(1, $email)->setMaxResults(1)->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}

		return $person;
	}

	public function searchByEmailStartingWith($email, $limit = null)
	{
		$email = str_replace(array('%', '_'), array('\\\\%', '\\\\_'), $email) . '%';

		return $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:Person p
			LEFT JOIN p.emails e
			WHERE e.email LIKE ?1
			ORDER BY p.id ASC
		")->setParameter(1, $email)->setMaxResults($limit)->execute();
	}

	public function searchByEmail($email, $limit = null)
	{
		$email = '%' . str_replace(array('%', '_'), array('\\\\%', '\\\\_'), $email) . '%';

		return $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:Person p
			LEFT JOIN p.emails e
			WHERE e.email LIKE ?1
			ORDER BY p.id ASC
		")->setParameter(1, $email)->setMaxResults($limit)->execute();
	}


	public function getPeopleFromIds(array $ids)
	{
		// Only valid ID's please :)
		// Do this because Doctrine doesnt have proper IN()
		// escaping until 2.1
		$ids = array_filter($ids, function ($val) {
			if (Numbers::isInteger($val)) {
				return true;
			}
			return false;
		});

		if (!$ids) return array();

		$people = $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:Person p INDEX BY p.id
			WHERE p.id IN(" . implode(',', $ids) . ")
			ORDER BY p.id ASC
		")->execute();

		return $people;
	}

	public function getPeopleResultsFromIds(array $ids)
	{
		if (!$ids) return array();

		$people = $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:Person p INDEX BY p.id
			WHERE p.id IN(?1)
			ORDER BY p.id ASC
		")->setParameter(1, $ids)
		  ->setFetchMode('DeskPRO:Person', 'emails', 'EAGER')
		  ->setFetchMode('DeskPRO:Person', 'primary_email', 'EAGER')
		  ->setFetchMode('DeskPRO:Person', 'custom_data', 'EAGER')
		  ->execute();

		return $people;
	}

	public function search($q, $limit = null)
	{
		$q = '%' . str_replace(array('%', '_'), array('\\\\%', '\\\\_'), $q) . '%';

		return $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:Person p
			LEFT JOIN p.emails e
			WHERE (p.name LIKE ?1) OR (p.first_name LIKE ?2) OR (p.last_name LIKE ?3) OR (e.email LIKE ?4)
			ORDER BY p.date_last_login DESC, p.id DESC
		")->setParameters(array(1=> $q, 2=> $q, 3=> $q, 4=>$q))->setMaxResults($limit)->execute();
	}


	public function getOrganizationMembers(OrganizationEntity $org)
	{
		return $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:Person p INDEX BY p.id
			WHERE p.organization = ?1
			ORDER BY p.last_name ASC, p.first_name ASC
		")->execute(array(1=> $org));
	}


	public function getUsergroupMembers(UsergroupEntity $ug)
	{
		return $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:Person p INDEX BY p.id
			LEFT JOIN p.usergroups ug
			WHERE ug.id = ?1
			ORDER BY p.last_name ASC, p.first_name ASC
		")->setParameter(1, $ug)->execute();
	}

	public function getUsergroupMemberIds(UsergroupEntity $ug)
	{
		return $this->getEntityManager()->getConnection()->fetchAllCol("
			SELECT person_id
			FROM person2usergroups
			WHERE usergroup_id = ?
		", array($ug->id));
	}


	/**
	 * @return void
	 */
	public function getChatAgentRoundRobin()
	{
		$active_agents_ids = App::getEntityRepository('DeskPRO:Session')->getAvailableAgentIds();

		// Count chats for each
		$chat_counts = App::getDb()->fetchAllKeyValue("
			SELECT agent_id, COUNT(*) as cnt
			FROM chat_conversations
			WHERE agent_id IS NOT NULL AND status = ?
			GROUP BY agent_id
			ORDER BY cnt DESC
		", array('open'));

		foreach ($active_agents_ids as $id) {
			if (!isset($chat_counts[$id])) {
				$chat_counts[$id] = 0;
			}
		}

		$grouped = array();
		foreach ($chat_counts as $id => $cnt) {
			if (!isset($grouped[$cnt])) $grouped[$cnt] = array();
			$grouped[$cnt][] = $id;
		}

		asort($chat_counts, SORT_NUMERIC);
		$bottom_group = array_shift($chat_counts);

		// Only one person
		if (count($bottom_group) == 1) {
			return App::findEntity('DeskPRO:Person', $bottom_group[0]);
		}

		// Otherwise, we'll fetch the person who hasnt had a chat in a while
		$id = App::getDb()->fetchColumn("
			SELECT agent_id
			FROM chat_conversations
			WHERE agent_id IN (" . implode(',', $bottom_group) . ") AND status = ?
			ORDER BY date_ended DESC
			LIMIT 1
		", array('ended'));

		return App::findEntity('DeskPRO:Person', $id);
	}

	/**
	 * Get a count of how many people there are
	 *
	 * @return int
	 */
	public function getCount()
	{
		return App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM people
		");
	}
}
