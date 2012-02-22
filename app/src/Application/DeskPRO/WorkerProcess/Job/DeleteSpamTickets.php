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
 * Goes through tickets marked as spam and deletes them
 */
class DeleteSpamTickets extends AbstractJob
{
	const DEFAULT_INTERVAL = 86400;

	public function run()
	{
		$secs = App::getSetting('core_tickets.spam_delete_time');

		// 0 means disable
		if ($secs < 1) {
			return;
		}

		$date_cut = date('Y-m-d H:i:s', time() - $secs);

		#------------------------------
		# find tickets to proc
		#------------------------------

		$ticket_ids = App::getDb()->fetchAllCol("
			SELECT id
			FROM tickets
			WHERE tickets.status = 'hidden' AND tickets.hidden_status = 'spam'
			AND tickets.date_status < ?
			LIMIT 5000
		", array($date_cut));

		foreach ($ticket_ids as $ticket_id) {

			App::getOrm()->beginTransaction();

			try {

				// See if the user has more tickets
				$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM tickets WHERE person_id = ?", array($ticket->person->id));

				$ticket = App::getEntityRepository('DeskPRO:Ticket')->find($ticket_id);
				App::getOrm()->remove($ticket);

				if ($count == 1) {
					App::getOrm()->remove($ticket->person);
				}

				App::getOrm()->flush();
				App::getOrm()->commit();
			} catch (\Exception $e) {
				App::getOrm()->rollback();
				throw $e;
			}
		}

		if ($ticket_ids) {
			$this->logStatus("Removed " . count($ticket_id) . " spam tickets");
			if ($user_count) {
				$this->logStatus("Removed " . count($user_count) . " users who had only one spam ticket");
			}
		}
	}
}
