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
 * @subpackage AdminBundle
 */

namespace Application\InstallBundle\Data;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\People\AgentPermissions\AgentPermissions;
use Application\DeskPRO\People\AgentPermissions\GroupsDbLoader;

/**
 * Scans the permtable template to extract the names of permissions so we can dynamically create
 * the "all" permission group.
 */
class AgentGroupPermScanner
{
	/**
	 * @var array
	 */
	protected $perm_names = null;

	protected function load()
	{
		if ($this->perm_names !== null) {
			return;
		}

		$perms = new AgentPermissions();

		$set_perms = array();
		foreach (GroupsDbLoader::$prefix_map as $real_name => $coll_name) {
			$obj = $perms->$coll_name;
			foreach ($obj->getNames() as $prop) {
				$set_perms[] = $real_name . '.' . $prop;
			}
		}

		$this->perm_names = $set_perms;
	}


	/**
	 * Get the names of all the permissions
	 *
	 * @return array
	 */
	public function getNames()
	{
		$this->load();
		return $this->perm_names;
	}
}
