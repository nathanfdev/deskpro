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
 * Cleans up stale email sources
 */
class CleanupEmailSources extends AbstractJob
{
	const DEFAULT_INTERVAL = 7200; // 2 hours

	public function run()
	{
		$snip = date('Y-m-d H:i:s', time() - App::getSetting('core.email_source_storetime'));
		$email_sources = App::getDb()->fetchAllCol("
			SELECT id
			FROM email_sources
			LEFT JOIN tickets_messages ON (tickets_messages.email_source_id = email_sources)
			LEFT JOIN tickets_messages_raw ON (tickets_messages_raw.message_id = tickets_messages.id)
			WHERE date_created < ? AND tickets_messages_raw.message_id IS NULL
			ORDER BY id ASC
			LIMIT 1000
		", array($snip));

		$num = 0;
		foreach ($email_sources as $source) {
			$desc = App::getApi('filestorage')->getFileDescriptor($source->blob->id);
			$desc->delete();

			App::getOrm()->detach($source);
			App::getOrm()->flush();

			$num++;
		}

		if ($num) {
			$this->logStatus("Cleaned up $num stale email sources");
		}
	}
}
