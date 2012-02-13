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
 * Cleanups to client_messages and client_channel_subscriptions
  */
class CleanupClientMessages extends AbstractJob
{
	const DEFAULT_INTERVAL = 60;

	public function run()
	{
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

		if ($num) {
			$this->logStatus("Cleaned up $num old client messages");
		}

		#------------------------------
		# client_channel_subscriptions
		#------------------------------

		$datetime = date('Y-m-d H:i:s', time() - 20); // 10 minutes
		$num = App::getDb()->executeUpdate("DELETE FROM client_channel_subscriptions WHERE date_ping < ?", array($datetime));

		if ($num) {
			$this->logStatus("Cleaned up $num stale client channel subscriptions");
		}
	}
}
