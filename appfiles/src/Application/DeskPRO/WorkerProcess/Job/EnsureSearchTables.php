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
		$has_one = App::getDb()->fetchColumn("SELECT id FROM tickets WHERE status IN ('awaiting_agent', 'pending') LIMIT 1");
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
