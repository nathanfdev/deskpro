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

namespace Application\DeskPRO\People\Helpers;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

/**
 * This helps working with agent teams on a person
 */
class AgentTeam implements \Orb\Helper\ShortCallableInterface
{
	protected $person;
	protected $_agent_team_ids = null;

	public function __construct(Entity\Person $person)
	{
		$this->person = $person;
	}

	public function getShortCallableNames()
	{
		return array(
			'getAgentTeamIds' => 'getAgentTeamIds',
		);
	}

	public function getAgentTeamIds()
	{
		if ($this->_agent_team_ids !== null) return $this->_agent_team_ids;

		$this->_agent_team_ids = App::getDb()->fetchAllCol("
			SELECT team_id
			FROM agent_team_members
			WHERE person_id = {$this->person['id']}
		");

		return $this->_agent_team_ids;
	}

	public function addToAgentTeam(Entity\AgentTeam $team)
	{
		return $team->addPerson($this);
	}

	public function reset()
	{
		$this->_agent_team_ids = null;
	}
}