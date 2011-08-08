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
 * This cleans up various temporary data
 */
class CleanupTmpData extends AbstractJob
{
	const DEFAULT_INTERVAL = 900; // 15 mins

	public function run()
	{
		$datetime = date('Y-m-d H:i:s', time());

		#------------------------------
		# Temp data
		#------------------------------
		
		$num = App::getDb()->executeUpdate("
			DELETE FROM tmp_data
			WHERE date_expire > ?",
		array($datetime));

		if ($num) {
			$this->logStatus("Cleaned up $num stale user temp data entries");
		}

		#------------------------------
		# Prefs
		#------------------------------

		$num = App::getDb()->executeUpdate("
			DELETE FROM people_prefs
			WHERE date_expire > ?",
		array($datetime));

		if ($num) {
			$this->logStatus("Cleaned up $num stale user preference entries");
		}
	}
}