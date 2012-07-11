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

		$date_cut = new \DateTime('@' . (time() - $secs));

		#------------------------------
		# find tickets to proc
		#------------------------------

		$ticket_count = 0;
		$user_count = 0;
		$open_trans = false;

		$all_tickets = App::getOrm()->createQuery("
			SELECT ticket, person
			FROM DeskPRO:Ticket ticket
			LEFT JOIN ticket.person person
			WHERE ticket.status = 'hidden' AND ticket.hidden_status = 'spam' AND ticket.date_status < ?0
		")->setMaxResults(1000)->execute(array($date_cut));

		$this->logger->log(sprintf("[DeleteSpamTickets] %d tickets to delete", count($all_tickets)), 'DEBUG');

		foreach ($all_tickets as $ticket) {

			if (!$open_trans) {
				$open_trans = true;
				App::getOrm()->beginTransaction();
			}

			try {

				$this->logger->log(sprintf("[DeleteSpamTickets] Deleted ticket %d", $ticket->id), 'DEBUG');

				// See if the user has only this one spam ticket
				$count = App::getDb()->fetchColumn("
					SELECT COUNT(*)
					FROM tickets
					WHERE person_id = ?
					LIMIT 2
				", array($ticket->person->id));

				App::getOrm()->remove($ticket);

				if ($count == 1) {
					$this->logger->log(sprintf("[DeleteSpamTickets] Deleted person %d", $ticket->person->id), 'DEBUG');

					$user_count++;
					App::getOrm()->remove($ticket->person);
				}

				$ticket_count++;
			} catch (\Exception $e) {
				if ($open_trans) {
					App::getOrm()->rollback();
				}
				$open_trans = false;
				throw $e;
			}

			if ($ticket_count % 250 == 0) {
				if ($open_trans) {
					App::getOrm()->commit();
				}
				$open_trans = false;
			}
		}

		if ($open_trans) {
			App::getOrm()->commit();
		}

		if ($ticket_count) {
			$this->logStatus("Removed " . count($ticket_count) . " spam tickets");
			if ($user_count) {
				$this->logStatus("Removed " . count($user_count) . " users who had only one spam ticket");
			}
		}
	}
}
