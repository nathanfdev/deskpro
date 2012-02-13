<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage WorkerProcess
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;

/**
 * Goes through soft-deleted tickets that were deleted long ago,
 * and permanantly removes them now.
 */
class HardDeleteTickets extends AbstractJob
{
	const DEFAULT_INTERVAL = 86400;

	public function run()
	{
		$secs = App::getSetting('core_tickets.hard_delete_time');

		// 0 means disable
		if ($secs < 1) {
			return;
		}

		$date_cut = date('Y-m-d H:i:s', time() - $secs);

		#------------------------------
		# find tickets to proc
		#------------------------------

		$ticket_ids = App::getDb()->fetchAllCol("
			SELECT tickets_deleted.ticket_id
			FROM tickets_deleted
			LEFT JOIN tickets ON (tickets.id = tickets_deleted.ticket_id)
			WHERE tickets_deleted.date_created < ?
			AND tickets.id IS NOT NULL
			LIMIT 5000
		", array($date_cut));

		foreach ($ticket_ids as $ticket_id) {

			App::getOrm()->beginTransaction();

			try {
				$ticket = App::getEntityRepository('DeskPRO:Ticket')->find($ticket_id);
				App::getOrm()->remove($ticket);
				App::getOrm()->commit();
			} catch (\Exception $e) {
				App::getOrm()->rollback();
				throw $e; // rethrow for error logging etc
			}
		}

		if ($ticket_ids) {
			$this->logStatus("Removed " . count($ticket_id) . " old soft-deleted tickets");
		}
	}
}
