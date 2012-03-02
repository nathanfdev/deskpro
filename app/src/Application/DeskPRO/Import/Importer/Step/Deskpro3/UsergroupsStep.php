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
 * @subpackage Import
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Application\DeskPRO\Entity\Usergroup;

class UsergroupsStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Usergroups';
	}

	public function run($page = 1)
	{
		$usergroups = $this->getOldDb()->fetchAll("SELECT * FROM user_groups ORDER BY id ASC");

		$this->logMessage(sprintf("Importing %d usergroups", count($usergroups)));
		if (!$usergroups) {
			return;
		}

		$start_time = microtime(true);

		$this->getDb()->beginTransaction();

		try {
			foreach ($usergroups as $group_info) {
				$this->processUsergroup($group_info);
			}

			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}

		$end_time = microtime(true);
		$this->logMessage(sprintf("Done all usergroups. Took %.3f seconds.", $end_time-$start_time));
	}

	protected function processUsergroup(array $group_info)
	{
		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('usergroup', $group_info['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$group_info['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		if ($group_info['system_name'] == 'guest') {
			return;
		} else {
			$usergroup = new Usergroup();
			$usergroup->title = $group_info['name'];
			$this->getEm()->persist($usergroup);
			$this->getEm()->flush();
		}

		// TODO:permissions mapping when permissions are final

		$this->saveMappedId('usergroup', $group_info['id'], $usergroup->id);

		if ($group_info['system_name'] == 'registered') {
			$this->saveMappedId('usergroup_sys', 'registered', $usergroup->id);
		}
	}
}
