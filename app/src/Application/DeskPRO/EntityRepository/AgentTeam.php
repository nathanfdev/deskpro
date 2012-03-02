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
use Application\DeskPRO\Entity\AgentTeam as AgentTeamEntity;

class AgentTeam extends AbstractEntityRepository
{
	protected $_team_names = null;

	protected function _loadTeamNames()
	{
		if ($this->_team_names !== null) return $this->_team_names;

		if (($this->_team_names = App::getCache('common')->load('agent_team_names')) === false) {
			$db = App::getDb();
			$this->_team_names = $db->fetchAllKeyValue("
				SELECT id, name
				FROM agent_teams
				ORDER BY name ASC
			");

			App::getCache('common')->save($this->_team_names, null, array('agent_teams'));
		}

		return $this->_team_names;
	}

	public function getTeamsFromIds(array $ids)
	{
		$ids = implode(',', $ids);

		$this->getEntityManager()->createQuery("
			SELECT t
			FROM DeskPRO:AgentTeam t
			WHERE t.id IN($ids)
		")->execute();
	}

	public function findByName($name)
	{
		try {
			$team = $this->getEntityManager()->createQuery("
				SELECT t
				FROM DeskPRO:AgentTeam t
				WHERE t.name LIKE ?1
			")->setParameter(1, "%$name%")->getSingleResult();
		} catch (\Exception $e) {
			return null;
		}

		return $team;
	}

	public function getTeamNames($for_ids = null)
	{
		$this->_loadTeamNames();

		if ($for_ids === null) {
			return $this->_team_names;
		}

		$ret = array();
		foreach ($for_ids as $id) {
			if (isset($this->_team_names[$id])) {
				$ret[] = $this->_team_names[$id];
			}
		}

		return $ret;
	}

	public function getMemberIds($team_id)
	{
		if ($team_id instanceof AgentTeamEntity) {
			$team_id = $team_id->id;
		}

		if (!is_array($team_id)) {
			$agent_ids = App::getDb()->fetchAllCol("
				SELECT person_id
				FROM agent_team_members
				WHERE team_id = ?
			", array($team_id));
		} else {
			$agent_ids = App::getDb()->fetchAllCol("
				SELECT person_id
				FROM agent_team_members
				WHERE team_id IN (" . implode(',', $team_id) . ")
			");
		}

		return $agent_ids;
	}

	/**
	 * Get all agents of all teams, and sort them into an array keyed
	 * by team: array('teamid' => array('agentid', 'agentid'))
	 *
	 * @return array
	 */
	public function getSortedMemberIds()
	{
		return App::getDb()->fetchAllGrouped("
			SELECT team_id, person_id
			FROM agent_team_members
		", array(), 'team_id', null, 'person_id');
	}

	public function getMembers($team)
	{
		$agent_ids = $this->getMemberIds($team);
		if (!$agent_ids) {
			return array();
		}

		$agent_ids = implode(',', $team);

		$agents = $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:Person p
			WHERE p.id IN ($agent_ids)
		")->execute();

		return $agents;
	}


	/**
	 * Get an array of all team IDs that the agents passed
	 * belong to. This is an all inclusive list and unsorted.
	 *
	 * @param $agents
	 * @return array
	 */
	public function getAllTeamIdsForAgents($agents)
	{
		$agent_ids = array();
		foreach ($agents as $a) {
			if (is_object($a)) {
				$agent_ids[] = $a['id'];
			} else {
				$agent_ids[] = $a;
			}
		}

		if (!$agent_ids) return array();
		$agent_ids = implode(',', $agent_ids);


		$team_ids = App::getDb()->fetchAllCol("
			SELECT team_id
			FROM agent_team_members
			WHERE person_id IN ($agent_ids)
			GROUP BY team_id
		");

		return $team_ids;
	}


	/**
	 * Gets an array of team ID's for each agent. Keyed
	 * by agent_id. Like getAllTeamIdsForAgents() except this
	 * is sorted into agents
	 *
	 * @param $agents
	 * @return array
	 */
	public function getTeamIdsForAgents($agents)
	{
		$agent_ids = array();
		foreach ($agents as $a) {
			if (is_object($a)) {
				$agent_ids[] = $a['id'];
			} else {
				$agent_ids[] = $a;
			}
		}

		if (!$agent_ids) return array();
		$agent_ids = implode(',', $agent_ids);

		$agent_teams = App::getDb()->fetchAllGrouped("
			SELECT person_id, team_id
			FROM agent_team_members
			WHERE person_id IN ($agent_ids)
		", array(), 'person_id', null, 'team_id');

		return $agent_teams;
	}


	public function getTeamToAgentsMap()
	{
		return App::getDb()->fetchAllGrouped("
			SELECT team_id, person_id
			FROM agent_team_members
		", array(), 'team_id', null, 'person_id');
	}


	/**
	 * Invalidates caches associated with agent teams
	 */
	public function invalidateCaches()
	{
		App::getCache('common')->clean('matchingTag', array('agent_teams'));
	}

	/**
	 * @see \Application\DeskPRO\DBAL\Logging\CacheInvalidor
	 * @param  $sql
	 * @return void
	 */
	public function invalidateFromQuery($sql)
	{
		$this->invalidateCaches();
	}
}
