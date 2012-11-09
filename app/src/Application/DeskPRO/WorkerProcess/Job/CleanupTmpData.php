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
			WHERE date_expire > ?
		", array($datetime));

		if ($num) {
			$this->logStatus("Cleaned up $num stale user temp data entries");
		}

		#------------------------------
		# Prefs
		#------------------------------

		$num = App::getDb()->executeUpdate("
			DELETE FROM people_prefs
			WHERE date_expire > ?
		", array($datetime));

		if ($num) {
			$this->logStatus("Cleaned up $num stale user preference entries");
		}

		#------------------------------
		# Log Items
		#------------------------------

		$datecut = date('Y-m-d H:i:s', time() - 259200);
		$num = App::getDb()->executeUpdate("
			DELETE FROM ticket_changetracker_logs
			WHERE date_created < ?
		", array($datecut));

		if ($num) {
			$this->logStatus("Cleaned up $num old ticket change tracker logs");
		}

		#------------------------------
		# Task queue logs Items
		#------------------------------

		$cutoff = 86400 * 14;
		$datecut = date('Y-m-d H:i:s', time() - $cutoff);
		$num = App::getDb()->executeUpdate("
			DELETE FROM task_queue
			WHERE status = 'completed' AND date_completed < ?
		", array($datecut));

		if ($num) {
			$this->logStatus("Cleaned up $num task queue logs");
		}

		#------------------------------
		# Ticket change logs
		#------------------------------

		$datecut = date('Y-m-d H:i:s', time() - 2592000); // 30 days


		#------------------------------
		# Try to delete old update status file
		#------------------------------

		if (file_exists(DP_WEB_ROOT.'/auto-update-status.php') && App::getSetting('core.last_auto_upgrade_time') < time()-180) {
			@unlink(DP_WEB_ROOT.'/auto-update-status.php');
		}
	}
}
