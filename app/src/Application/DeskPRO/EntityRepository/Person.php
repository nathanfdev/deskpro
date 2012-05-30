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
use Application\DeskPRO\Entity\Organization as OrganizationEntity;
use Application\DeskPRO\Entity\Usergroup as UsergroupEntity;

use Orb\Util\Numbers;

class Person extends AbstractEntityRepository
{
	protected $identity_helper;

	/**
	 * @return \Application\DeskPRO\EntityRepository\Helper\IdentityHelper
	 */
	public function getIdentityHelper()
	{
		if (!$this->identity_helper) {
			$this->identity_helper = new Helper\IdentityHelper($this->getEntityManager(), $this);
		}

		return $this->identity_helper;
	}

	public function getAgents()
	{
		if (($agents = $this->getIdentityHelper()->getCollection('agents')) === null) {
			$agents = $this->getEntityManager()->createQuery("
				SELECT p
				FROM DeskPRO:Person p INDEX BY p.id
				WHERE p.is_agent = true
				ORDER BY p.first_name ASC, p.last_name ASC
			")->execute();

			$this->getIdentityHelper()->setCollectionFromResults('agents', $agents);
		}

		return $agents;
	}

	public function getAgent($id)
	{
		$agents = $this->getAgents();

		return $agents[$id];
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


	/**
	 * Get agent names
	 *
	 * @param null $for_ids
	 * @return mixed
	 */
	public function getAgentNames($for_ids = null)
	{
		$names = array();

		if ($for_ids && !is_array($for_ids)) {
			$for_ids = array($for_ids);
		}

		// No names to return
		if (is_array($for_ids) && !$for_ids) {
			return array();
		}

		foreach ($this->getAgents() as $agent) {
			if ($for_ids && !in_array($agent->id, $for_ids)) {
				continue;
			}
			$names[$agent->getId()] = $agent->getDisplayName();
		}

		return $names;
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
		if (!$ids) return array();

		$people = $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:Person p INDEX BY p.id
			WHERE p.id IN(?0)
			ORDER BY p.id ASC
		")->execute(array($ids));

		return $people;
	}

	public function getPeopleResultsFromIds(array $ids)
	{
		if (!$ids) return array();

		$people = $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:Person p INDEX BY p.id
			WHERE p.id IN(?0)
			ORDER BY p.id ASC
		")->execute(array($ids));

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


	/**
	 * Get the count of users awaiting validation by agnets
	 *
	 * @return int
	 */
	public function getAgentValidatingCount()
	{
		return App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM people
			WHERE is_agent_confirmed = 0
		");
	}
}
