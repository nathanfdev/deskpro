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

class CleanupAlways extends AbstractJob
{
	const DEFAULT_INTERVAL = 1;

	public function run()
	{
		#------------------------------
		# cleanup chat pings
		#------------------------------

		$cutoff = time() - 180;

		App::getDb()->executeUpdate("
			DELETE FROM chat_conversation_pings
			WHERE ping_time < $cutoff
		");

		#------------------------------
		# client_messages
		#------------------------------

		// client messages are nearly instant, so this timesnip is very low
		$datetime = date('Y-m-d H:i:s', time() - 120);

		$long_lived_channels = array(
			'agent_chat.new-message'
		);

		$long_lived_channels = "'" . implode("','", $long_lived_channels) . "'";

		App::getDb()->beginTransaction();

		try {
			$num = App::getDb()->executeUpdate("
				DELETE FROM client_messages
				WHERE
					date_created < ? AND channel NOT IN ($long_lived_channels)
			", array($datetime));

				// Long-lived channels are still only deleted after 3 days
				$datetime = date('Y-m-d H:i:s', time() - 259200);
				$num += App::getDb()->executeUpdate("
				DELETE FROM client_messages
				WHERE
					date_created < ? AND channel IN ($long_lived_channels)
			", array($datetime));

				App::getDb()->commit();
		} catch (\Exception $e) {
			App::getDb()->rollback();
			throw $e;
		}

		if ($num) {
			$this->logStatus("Cleaned up $num old client messages");
		}

		#------------------------------
		# client_channel_subscriptions
		#------------------------------

		$datetime = date('Y-m-d H:i:s', time() - 600); // 10 minutes
		$num = App::getDb()->executeUpdate("DELETE FROM client_channel_subscriptions WHERE date_ping < ?", array($datetime));

		if ($num) {
			$this->logStatus("Cleaned up $num stale client channel subscriptions");
		}

		#------------------------------
		# Try to delete old update status file
		#------------------------------

		if (file_exists(DP_WEB_ROOT.'/auto-update-status.php') && App::getSetting('core.last_auto_upgrade_time') < time()-180) {
			@unlink(DP_WEB_ROOT.'/auto-update-status.php');
		}
	}
}
