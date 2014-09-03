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
 * @subpackage
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;

class AgentDataService
{
	/**
	 * @var bool
	 */
	protected $has_init = false;

	/**
	 * @var bool
	 */
	protected $has_init_teammap = false;

	/**
	 * @var \Application\DeskPRO\Entity\Person[]
	 */
	public $agents = array();

	/**
	 * @var array
	 */
	private $agent_teams = array();

	/**
	 * @var array
	 */
	private $agent_to_teams = array();

	/**
	 * @var array
	 */
	private $team_to_agents = array();

	/**
	 * @var array
	 */
	private $agent_to_groups = array();

	/**
	 * @var array
	 */
	public $online_agent_ids;

	/**
	 * @var int[]
	 */
	public $ids = array();

	/**
	 * @var
	 */
	public $team_ids;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var int
	 */
	protected $agent_timeout = 20;

	public static function create(DeskproContainer $container, array $options = null)
	{
		$em = $container->getEm();
		$o = new static($em);
		return $o;
	}

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->db = $em->getConnection();
	}

	protected function preload()
	{
		if ($this->has_init) {
			return;
		}
		$this->has_init = true;

		$this->agents = $this->em->getRepository('DeskPRO:Person')->getAgents();
		foreach ($this->agents as $a) {
			$this->ids[] = $a->getId();
		}

		$this->agent_teams = $this->em->getRepository('DeskPRO:AgentTeam')->getTeams();
		$this->agent_teams = Arrays::keyFromData($this->agent_teams, 'id');
		$this->team_ids = array_keys($this->agent_teams);
	}

	protected function preloadTeamMap()
	{
		if ($this->has_init_teammap) {
			return;
		}
		$this->has_init_teammap = true;

		$this->team_to_agents = $this->db->fetchAllGrouped("
			SELECT team_id, person_id
			FROM agent_team_members
		", array(), 'team_id', null, 'person_id');

		$this->agent_to_teams = Arrays::reverseLookupArray($this->team_to_agents, true);

		if ($this->ids) {
			$this->agent_to_groups = $this->db->fetchAllGrouped("
				SELECT person_id, usergroup_id
				FROM person2usergroups
				WHERE person_id IN (" . implode(',', $this->ids) . ")
			", array(), 'person_id', null, 'usergroup_id');
		}
	}


	/**
	 * @return \Application\DeskPRO\Entity\Person[]
	 */
	public function getAgents()
	{
		$this->preload();
		return $this->agents;
	}


	/**
	 * @return \Application\DeskPRO\Entity\AgentTeam[]
	 */
	public function getAgentTeams()
	{
		$this->preload();
		return $this->agent_teams;
	}


	/**
	 * @param array $for_ids
	 * @return string[]
	 */
	public function getNames(array $for_ids = null)
	{
		$ret = array();

		if ($for_ids) {
			foreach ($this->getAgents() as $agent) {
				if ($for_ids === null || in_array($agent->getId(), $for_ids)) {
					$ret[$agent->getId()] = $agent->getDisplayName();
				}
			}
		}

		return $ret;
	}


	/**
	 * @return int[]
	 */
	public function getIds()
	{
		$this->preload();
		return $this->ids;
	}


	/**
	 * @return int[]
	 */
	public function getTeamIds()
	{
		$this->preload();
		return $this->team_ids;
	}


	/**
	 * @param int $id
	 * @return \Application\DeskPRO\Entity\Person|null
	 */
	public function get($id)
	{
		$this->preload();

		if (isset($this->agents[$id])) {
			return $this->agents[$id];
		}

		return null;
	}


	/**
	 * @param int $id
	 * @return bool
	 */
	public function has($id)
	{
		$this->preload();

		return isset($this->agents[$id]);
	}


	/**
	 * Get an array of agents by ids
	 *
	 * @param array $ids
	 * @return array
	 */
	public function getByIds($ids)
	{
		$this->preload();

		$agents = array();

		foreach ($ids as $id) {
			$id = (int)$id;
			if (isset($this->agents[$id])) {
				$agents[$id] = $this->agents[$id];
			}
		}

		return $agents;
	}


	/**
	 * Returns an array of valid agent IDs in $ids. Optionally
	 * specify $invalid and all invalid IDs will be put into it.
	 *
	 * @param array $ids
	 * @param null $invalid_ids
	 * @return array
	 */
	public function confirmAgentIds(array $ids, &$invalid_ids = null)
	{
		$this->preload();

		$valid_ids = array();
		if (!isset($invalid_ids) || !$invalid_ids) {
			$invalid_ids = array();
		}

		foreach ($ids as $id) {
			if (isset($this->agents[$id])) {
				$valid_ids[] = $id;
			} else {
				$invalid_ids[] = $id;
			}
		}

		return $valid_ids;
	}


	/**
	 * @param string $email
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function getByEmail($email)
	{
		foreach ($this->getAgents() as $agent) {
			if ($agent->hasEmailAddress($email)) {
				return $agent;
			}
		}

		return null;
	}


	/**
	 * Get an array of agents who are online now (have active sessions).
	 *
	 * @return int[]
	 */
	public function getOnlineAgentIds()
	{
		if ($this->online_agent_ids !== null) {
			return $this->online_agent_ids;
		}
		$cutoff = date('Y-m-d H:i:s', time() - $this->agent_timeout);

		$this->online_agent_ids = $this->db->fetchAllKeyValue("
			SELECT DISTINCT s.person_id
			FROM sessions s
			INNER JOIN people p ON (s.person_id = p.id)
			WHERE p.is_agent = 1 AND p.is_deleted = 0 AND s.date_last > ?
		", array($cutoff), 0, 0);

		return $this->online_agent_ids;
	}


	/**
	 * @return array
	 */
	public function getOnlineAgents()
	{
		$this->getOnlineAgentIds();

		$agents = array();
		foreach ($this->online_agent_ids as $id) {
			$agents[$id] = $this->get($id);
		}

		return $agents;
	}


	/**
	 * Check if an agent is online
	 *
	 * @param int|Person $id_or_agent
	 * @return bool
	 */
	public function isAgentOnline($id_or_agent)
	{
		$this->getOnlineAgentIds();
		$id = is_object($id_or_agent) ? $id_or_agent->getId() : $id_or_agent;

		return isset($this->online_agent_ids[$id]);
	}


	/**
	 * Count how many agents are currently online
	 *
	 * @return int
	 */
	public function countOnlineAgents()
	{
		return count($this->online_agent_ids);
	}


	/**
	 * @param int $id
	 * @return \Application\DeskPRO\Entity\AgentTeam|null
	 */
	public function getTeam($id)
	{
		$this->preload();

		if (isset($this->agent_teams[$id])) {
			return $this->agent_teams[$id];
		}

		return null;
	}


	/**
	 * @param int $id
	 * @return bool
	 */
	public function hasTeam($id)
	{
		$this->preload();

		return isset($this->agent_teams[$id]);
	}


	/**
	 * Get an array of agents by ids
	 *
	 * @param array $ids
	 * @return array
	 */
	public function getTeamsByIds($ids)
	{
		$this->preload();

		$teams = array();

		foreach ($ids as $id) {
			$id = (int)$id;
			if (isset($this->agent_teams[$id])) {
				$teams[$id] = $this->agent_teams[$id];
			}
		}

		return $teams;
	}


	/**
	 * @param int|\Application\DeskPRO\Entity\Person $agent
	 * @return \Application\DeskPRO\Entity\AgentTeam[]
	 * @throws \InvalidArgumentException
	 */
	public function getTeamsForAgent($agent)
	{
		$this->preload();
		$this->preloadTeamMap();

		$aid = is_object($agent) ? $agent->id : $agent;
		$agent = $this->get($aid);

		if (!$agent) {
			throw new \InvalidArgumentException;
		}

		if (empty($this->agent_to_teams[$agent->id])) {
			return array();
		}

		$teams = array();
		foreach ($this->agent_to_teams[$agent->id] as $tid) {
			$t = $this->getTeam($tid);
			if ($t) {
				$teams[] = $t;
			}
		}

		return $teams;
	}

	/**
	 * @param int|\Application\DeskPRO\Entity\Person $agent
	 * @return \Application\DeskPRO\Entity\AgentTeam[]
	 * @throws \InvalidArgumentException
	 */
	public function getGroupIdsForAgent($agent)
	{
		$this->preload();
		$this->preloadTeamMap();

		$aid = is_object($agent) ? $agent->id : $agent;
		$agent = $this->get($aid);

		if (!$agent) {
			throw new \InvalidArgumentException;
		}

		if (empty($this->agent_to_groups[$agent->id])) {
			return array();
		}

		return $this->agent_to_groups[$agent->id];
	}


	/**
	 * @param int|\Application\DeskPRO\Entity\AgentTeam $team
	 * @return \Application\DeskPRO\Entity\Person[]
	 * @throws \InvalidArgumentException
	 */
	public function getAgentsForTeam($team)
	{
		$this->preload();
		$this->preloadTeamMap();

		$tid = is_object($team) ? $team->id : $team;
		$team = $this->getTeam($tid);

		if (!$team) {
			throw new \InvalidArgumentException;
		}

		if (empty($this->team_to_agents[$team->id])) {
			return array();
		}

		$agents = arary();
		foreach ($this->team_to_agents[$team->id] as $tid) {
			$t = $this->get($tid);
			if ($t) {
				$agents[] = $t;
			}
		}

		return $agents;
	}


	/**
	 * Selects agents based on some kind of selector:
	 *
	 * - ticket_agent:          The assigned agent
	 * - ticket_team:           Agents of the assigned team
	 * - ticket_agent_teams:    Agents of the teams of the assigned agent
	 * - ticket_followers:      Agent followers
	 * - ticket_follower_teams: Teams of the current followers
	 * - person:                The current person performer
	 * - person_teams:          Teams of the current person performer
	 * - all_agents:            All agents
	 * - agent:10               A specific agent
	 * - team:12                Agents of a specific team
	 *
	 * @param string $selector        Keyword or agent id
	 * @param Person $person_context  Current person performer
	 * @param Ticket $ticket_context  Current ticket context
	 * @return array
	 */
	public function selectAgents($selector, Person $person_context = null, Ticket $ticket_context = null)
	{
		$return = array();

		// Legacy terms
		switch ($selector) {
			case -1:          $selector = 'person'; break;
			case 'agent':     $selector = 'ticket_agent'; break;
			case 'team':      $selector = 'ticket_team'; break;
			case 'followers': $selector = 'ticket_followers'; break;
		}

		if (is_numeric($selector)) {
			$selector = 'agent:' . $selector;
		}

		if (strpos($selector, ':')) {
			list ($type, $option) = explode(':', $selector, 2);
		} else {
			$type = $selector;
			$option = null;
		}

		switch ($type) {
			// Ticket agent
			case 'ticket_agent':
				if ($ticket_context && $ticket_context->agent) {
					$return[] = $ticket_context->agent;
				}
				break;

			// Teams of assigned agent
			case 'ticket_agent_teams':
				if ($ticket_context && $ticket_context->agent) {
					$teams = $this->getTeamsForAgent($ticket_context->agent);
					foreach ($teams as $t) {
						$return = array_merge($return, $this->getAgentsForTeam($t));
					}
				}
				break;

			// Agents of assigned team
			case 'ticket_team':
				if ($ticket_context && $ticket_context->agent_team) {
					$return = $this->getAgentsForTeam($ticket_context->agent_team);
				}
				break;


			// Agent followers
			case 'ticket_followers':
				if ($ticket_context) {
					$return = $ticket_context->getAgentParticipants();
				}
				break;

			// Agents of teams of follower
			case 'ticket_follower_teams':
				if ($ticket_context) {
					$followers = $ticket_context->getAgentParticipants();
					foreach ($followers as $a) {
						$teams = $this->getTeamsForAgent($a);
						if ($teams) {
							foreach ($teams as $t) {
								$return = array_merge($return, $this->getAgentsForTeam($t));
							}
						}
					}
				}
				break;

			// Current person
			case 'person':
				if ($person_context && $person_context->is_agent) {
					$return[] = $person_context;
				}
				break;

			// Teams of current person
			case 'person_teams':
				if ($person_context && $person_context->is_agent) {
					$teams = $this->getTeamsForAgent($person_context);
					foreach ($teams as $t) {
						$return = array_merge($return, $this->getAgentsForTeam($t));
					}
				}
				break;

			// All agents
			case 'all_agents':
				$return = $this->getAgents();
				break;

			// Specific agent
			case 'agent':
				$agent = $this->get($option);
				if ($agent) {
					$return[] = $agent;
				}
				break;

			// Specific team
			case 'team':
				$team = $this->getTeam($option);
				if ($team) {
					$return = array_merge($return, $this->getAgentsForTeam($team));
				}
				break;
		}

		return $return;
	}
}