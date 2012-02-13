<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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

		// TODO permission mapping when permissions are final

		$this->saveMappedId('usergroup', $group_info['id'], $usergroup->id);

		if ($group_info['system_name'] == 'registered') {
			$this->saveMappedId('usergroup_sys', 'registered', $usergroup->id);
		}
	}
}
