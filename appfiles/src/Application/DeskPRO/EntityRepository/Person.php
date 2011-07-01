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

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;
use \Orb\Util\Numbers;

class Person extends EntityRepository
{
	protected $_agent_names = null;

	protected function _loadAgentNames()
	{
		if ($this->_agent_names !== null) return;

		$db = App::getDb();
		$this->_agent_names = $db->fetchAllKeyValue("
			SELECT id, CONCAT_WS(' ', first_name, last_name) AS full_name
			FROM people
			WHERE is_agent = 1
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
		foreach ($for_ids as $id) {
			if (isset($this->_agent_names[$id])) {
				$ret[$id] = $this->_agent_names[$id];
			}
		}

		return $ret;
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
}