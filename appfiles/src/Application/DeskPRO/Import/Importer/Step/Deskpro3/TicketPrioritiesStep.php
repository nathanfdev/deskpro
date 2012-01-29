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

use Application\DeskPRO\Entity\TicketPriority;

class TicketPrioritiesStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Ticket Priorities';
	}

	public function run($page = 1)
	{
		$count = $this->getOldDb()->fetchAll("SELECT COUNT(*) FROM ticket_pri");
		$this->logMessage(sprintf("Importing %d ticket priorities", $count));
		if (!$count) {
			return;
		}

		$priorities = $this->getOldDb()->fetchAll("SELECT * FROM ticket_pri ORDER BY id ASC");

		$start_time = microtime(true);

		$this->getDb()->beginTransaction();

		try {
			foreach ($priorities as $pri) {
				$this->processPriority($pri);
			}

			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}

		$end_time = microtime(true);
		$this->logMessage(sprintf("Done all priorities. Took %.3f seconds.", $end_time-$start_time));
	}

	protected function processPriority(array $pri)
	{
		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('ticket_priority', $pri['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$pri['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$new_pri = new TicketPriority();
		$new_pri->title = $pri['name'];
		$new_pri->priority = $pri['displayorder'];
		$new_pri->display_order = $pri['displayorder'];

		$this->getEm()->persist($new_pri);
		$this->getEm()->flush();

		$this->saveMappedId('ticket_priority', $pri['id'], $new_pri->id);
	}
}
