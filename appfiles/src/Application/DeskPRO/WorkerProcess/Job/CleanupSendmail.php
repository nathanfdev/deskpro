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
 * This cleans up various temporary data
 */
class CleanupSendmail extends AbstractJob
{
	const DEFAULT_INTERVAL = 43200; // half a day

	public function run()
	{
		$days = App::getSetting('core.store_sent_mail_days');

		if (!$days) {
			$num = App::getDb()->executeUpdate("
				DELETE FROM sendmail_queue
				WHERE has_sent = 1
			");
		} else {
			$datetime = date('Y-m-d H:i:s', "-$days days");
			$num = App::getDb()->executeUpdate("
				DELETE FROM sendmail_queue
				WHERE has_sent = 1 AND date_sent < ?",
			array($datetime));
		};

		if ($num) {
			$this->logStatus("Cleaned up $num sent emails");
		}
	}
}
