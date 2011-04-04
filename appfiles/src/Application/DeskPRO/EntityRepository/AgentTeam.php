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