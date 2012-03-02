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
class CleanupTmpAttach extends AbstractJob
{
	const DEFAULT_INTERVAL = 900; // 15 mins

	public function run()
	{
		$now = date('Y-m-d H:i:s');
		$datetime = date('Y-m-d H:i:s', strtotime('-6 hours'));

		$blob_ids = App::getDb()->fetchAllCol("
			SELECT id
			FROM blobs
			WHERE (is_temp = 1 AND date_created < ?) OR date_cleanup < ?
		", array($datetime, $now));

		$num = 0;
		foreach ($blob_ids as $blob_id) {
			$desc = App::getApi('filestorage')->getFileDescriptor($blob_id);
			$desc->delete();
			$num++;
		}

		if ($num) {
			$this->logStatus("Cleaned up $num temporary attachments");
		}
	}
}
