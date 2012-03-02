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
 * @subpackage WorkerProcess
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
