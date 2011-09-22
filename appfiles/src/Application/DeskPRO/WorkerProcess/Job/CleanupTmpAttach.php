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
class CleanupTmpAttach extends AbstractJob
{
	const DEFAULT_INTERVAL = 900; // 15 mins

	public function run()
	{
		$datetime = date('Y-m-d H:i:s', strtotime('-6 hours'));

		$blob_ids = App::getDb()->fetchAllCol("
			SELECT id
			FROM blobs
			WHERE is_temp = 1 AND date_created < ?
		", array($datetime));

		$num = 0;
		foreach ($blob_ids as $blob_id) {
			try {
				$desc = App::getApi('filestorage')->getFileDescriptor($blob_id);
				$desc->delete();
				$num++;
			} catch (\Exception $e) {}
		}

		if ($num) {
			$this->logStatus("Cleaned up $num stale user preference entries");
		}
	}
}
