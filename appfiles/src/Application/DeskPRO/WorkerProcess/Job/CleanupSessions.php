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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Log\Logger;

/**
 * This just cleans up various records
 */
class CleanupSessions extends AbstractJob
{
	const DEFAULT_INTERVAL = 3600;

	public function run()
	{
		$datetime = date('Y-m-d H:i:s', time() - App::getSetting('core.sessions_lifetime'));
		$num = App::getDb()->executeUpdate("DELETE FROM sessions WHERE date_last < ?", array($datetime));

		if ($num) {
			$this->logStatus("Cleaned up $num stale sessions");
		}
	}
}