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
use Application\DeskPRO\Entity\Article;

/**
 * Archives old tickets
 */
class ArchiveTickets extends AbstractJob
{
	const DEFAULT_INTERVAL = 14400; // 4 hours

	public function run()
	{
		if (!App::getSetting('core_tickets.use_archive')) {
			return;
		}

		$datecut = new \DateTime('-' . App::getSetting('core_tickets.auto_archive_time') . ' seconds');
		$datecut = $datecut->format('Y-m-d H:i:s');

		$num = App::getDb()->executeUpdate("
			UPDATE tickets
			SET status = 'closed'
			WHERE status = 'resolved' AND date_resolved < ?
		", array($datecut));

		App::getDb()->executeUpdate("
			DELETE FROM tickets_search_active
			WHERE status = 'resolved' AND date_resolved < ?
		");

		if ($num) {
			$this->logStatus("$num tickets archived");
		}
	}
}
