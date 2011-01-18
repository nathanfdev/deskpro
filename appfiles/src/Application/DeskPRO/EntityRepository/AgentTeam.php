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

class AgentTeam extends EntityRepository
{
	protected $_team_names = null;

	/**
	 * @return array
	 */
	public function getTeamNames()
	{
		if ($this->_team_names !== null) return $this->_team_names;

		$db = App::getDb();
		$this->_team_names = $db->fetchAllKeyValue("
			SELECT id, name
			FROM agent_teams
			ORDER BY name ASC
		");

		return $this->_team_names;
	}
}