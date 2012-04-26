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
 * Cleans up old stat data
 */
class CleanupStats extends AbstractJob
{
	const DEFAULT_INTERVAL = 10800; // 3 hours

	public function run()
	{
		$dashboard_stats = App::getOrm()->createQuery("
			SELECT ds, s
			FROM DeskPRO:ReportDashboardStat ds
			LEFT JOIN ds.stat s
		")->execute();

		$num = 0;
		foreach ($dashboard_stats as $ds) {
			switch ($ds->stat->run_frequency) {
				case 'hourly':
					$time_multiplier = 3600;
					break;
				case 'daily':
					$time_multiplier = 86400;
					break;
				case 'monthly':
					$time_multiplier = 2592000;
					break;
				case 'yearly':
					$time_multiplier = 31536000;
					break;
				default:
					$time_multiplier = 0;
			}

			if (!$time_multiplier) {
				continue;
			}

			$time_back = $ds->number_data_points * $time_multiplier;
			$cut = date('Y-m-d H:i:s', time() - $time_back);

			$num += App::getDb()->executeUpdate("
				DELETE FROM stat_value
				WHERE stat_id = ? AND stat_unix < ?
			", array($ds->stat->getId(), $cut));
		}

		if ($num) {
			$this->logStatus("Cleaned up $num old stat records");
		}
	}
}
