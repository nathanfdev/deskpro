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
 */

namespace Application\DeskPRO\Groups;

use Application\DeskPRO\DBAL\Connection;

class PermissionsLoader
{
	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	private $db;

	/**
	 * @var array
	 */
	private $ug_perms;

	/**
	 * @var array
	 */
	private $agent_override_perms;


	/**
	 * @param Connection $db
	 */
	public function __construct(Connection $db)
	{
		$this->db = $db;
	}


	/**
	 * @return array
	 */
	public function getAllPermissions()
	{
		if ($this->ug_perms !== null) {
			return $this->ug_perms;
		}

		$this->ug_perms = $this->db->fetchAllGrouped("
			SELECT usergroup_id, name, value
			FROM permissions
			WHERE person_id IS NULL
		", array(), 'usergroup_id');

		return $this->ug_perms;
	}


	/**
	 * @param array $ug_ids
	 * @return array
	 */
	public function getUsergroupPermissions(array $ug_ids)
	{
		$ug_ids = array_fill_keys($ug_ids, true);

		$ret = array();
		foreach ($this->getAllPermissions() as $ugid => $p) {
			if (isset($ug_ids[$ugid])) {
				$ret[$ugid] = $p;
			}
		}

		return $ret;
	}


	/**
	 * @return array
	 */
	public function getAllAgentOverridePermissions()
	{
		if ($this->agent_override_perms !== null) {
			return $this->agent_override_perms;
		}

		$this->agent_override_perms = $this->db->fetchAllGrouped("
			SELECT person_id, name, value
			FROM permissions
			WHERE person_id IS NOT NULL
		", array(), 'person_id');

		return $this->agent_override_perms;
	}


	/**
	 * @param array $ug_ids
	 * @return array
	 */
	public function getAgentOverridePermissions(array $agent_ids)
	{
		$agent_ids = array_fill_keys($agent_ids, true);

		$ret = array();
		foreach ($this->getAllAgentOverridePermissions() as $aid => $p) {
			if (isset($agent_ids[$aid])) {
				$ret[$aid] = $p;
			}
		}

		return $ret;
	}
}