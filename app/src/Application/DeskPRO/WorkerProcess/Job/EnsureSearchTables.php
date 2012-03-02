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
 * Goes through queued messages
 */
class EnsureSearchTables extends AbstractJob
{
	const DEFAULT_INTERVAL = 60;

	public function run()
	{
		// Check to see if the search table isnt already filled
		$has_one = App::getDb()->fetchColumn("SELECT id FROM tickets_search_active LIMIT 1");
		if ($has_one) {
			return;
		}

		// Check to see if the search table is legitamtely empty (ie no tickets need attn)
		$has_one = App::getDb()->fetchColumn("SELECT id FROM tickets WHERE status IN ('awaiting_agent', 'awaiting_user') LIMIT 1");
		if (!$has_one) {
			return;
		}

		// If we got this far then we need to refill the table,
		// ie after a server reboot
		App::getEntityRepository('DeskPRO:Ticket')->fillSearchTable();

		$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM tickets_search_active");
		$this->logStatus("Filled tickets_search_active table with {$count} tickets");
	}
}
