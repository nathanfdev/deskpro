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
 * Cleanups to client_messages and client_channel_subscriptions
 *
 * Both are quite short-lived at 10 minutes.
 */
class CleanupSessions extends AbstractJob
{
	public function run()
	{
		#------------------------------
		# client_messages
		#------------------------------

		$datetime = date('Y-m-d H:i:s', time() - 600); // 10 minutes
		$num = App::getDb()->executeUpdate("DELETE FROM client_messages WHERE date_created < ?", array($datetime));

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
	}
}