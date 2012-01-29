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

class TicketCategoriesStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Ticket Categories';
	}

	public function run($page = 1)
	{
		$count = $this->getOldDb()->fetchAll("SELECT COUNT(*) FROM ticket_cat");
		$this->logMessage(sprintf("Importing %d ticket categories", $count));
		if (!$count) {
			return;
		}

		$categories_top = $this->getOldDb()->fetchAll("SELECT * FROM ticket_cat WHERE parent = 0 ORDER BY id ASC");
		$categories_sub = $this->getOldDb()->fetchAll("SELECT * FROM ticket_cat WHERE parent != 0 ORDER BY id ASC");

		$start_time = microtime(true);

		$this->getDb()->beginTransaction();

		try {
			// First do the parents ....
			foreach ($categories_top as $cat) {
				$this->processCategory($cat);
			}

			// Then the children after. Easiest way to make sure a parent exists before the child
			// is to just do them separately like this.
			foreach ($categories_sub as $cat) {
				$this->processCategory($cat);
			}

			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}

		$end_time = microtime(true);
		$this->logMessage(sprintf("Done all categories. Took %.3f seconds.", $end_time-$start_time));
	}

	protected function processCategory(array $cat)
	{
		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('ticket_category', $cat['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$cat['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$dep = new Department();
		$dep->title = $cat['name'];
		$dep->display_order = $cat['displayorder'];
		$dep->is_tickets_enabled = true;
		$dep->is_chat_enabled = true;

		if ($cat['parent']) {
			$parent = $this->getEm()->find('DeskPRO:Department', $this->getMappedNewId('ticket_category', $cat['parent']));
			if ($parent) {
				$dep->parent = $parent;
			}
		}

		$this->getEm()->persist($dep);
		$this->getEm()->flush();

		$this->saveMappedId('ticket_category', $cat['id'], $dep->id);
	}
}
