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

use Application\DeskPRO\Entity\Department;

class UserChatDepartmentsStep extends AbstractDeskpro3Step
{
	/**
	 * Existing departments read in
	 * @var array
	 */
	protected $departments;

	public static function getTitle()
	{
		return 'Import Chat Departments';
	}

	public function run($page = 1)
	{
		$start_time = microtime(true);

		$this->departments = $this->getDb()->fetchAllKeyValue("SELECT id, title FROM departments WHERE parent_id IS NULL");
		foreach ($this->departments as &$title) {
			$title = strtolower($title);
		}

		$chat_deps = $this->getOldDb()->fetchAll("SELECT * FROM chat_dep ORDER BY displayorder ASC");

		$this->getDb()->beginTransaction();

		try {
			foreach ($chat_deps as $chat_dep) {
				$this->processCategory($chat_dep);
			}

			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}

		$end_time = microtime(true);
		$this->logMessage(sprintf("Done all categories. Took %.3f seconds.", $end_time-$start_time));
	}

	protected function processCategory(array $chat_dep)
	{
		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('chat_dep', $chat_dep['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$chat_dep['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Try to find a name match on existing departments
		# that would have been imported from tickets before
		#------------------------------

		$title_l = strtolower($chat_dep['name']);
		foreach ($this->departments as $id => $dep_title) {
			if ($dep_title == $title_l) {
				// We found a match, so just map this chat department to th existing one,
				// and enable chat app on it
				$this->saveMappedId('chat_dep', $chat_dep['id'], $id);
				$this->getDb()->update('departments', array(
					'is_chat_enabled' => 1
				), array('id' => $chat_dep['id']));
				return;
			}
		}

		#------------------------------
		# Have to create the department
		#------------------------------

		$dep = new Department();
		$dep->title = $chat_dep['name'];
		$dep->display_order = '1' . $chat_dep['displayorder'];
		$dep->is_chat_enabled = true;

		$this->getEm()->persist($dep);
		$this->getEm()->flush();

		$this->saveMappedId('chat_dep', $chat_dep['id'], $dep->id);
	}
}



