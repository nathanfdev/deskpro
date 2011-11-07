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
use Application\DeskPRO\Entity\Stat;

/**
 * Generate stats for the reporting system
 */
class GenerateStats extends AbstractJob
{
	const DEFAULT_INTERVAL = 3600; // 1 hour

	public function run()
	{
		$time = time();
		
		// Get the stats
		$stats 		= App::getEntityRepository('DeskPRO:Stat')
				     ->getStatsRequiringUpdate($time);
						
		$count_stats 	= count($stats);
		
		$this->processStats($stats);
		
		$msg = "Generate Stats ($count_stats)";
		$this->logStatus($msg);
	}
	
	/**
	 * Process a list of stats
	 *
	 * @param array $stats  List of stats to process
	 */
	protected function processStats($stats)
	{
		foreach ($stats as $stat) {
			$this->processStats($stat);
		}
	}
	
	/**
	 * Process an individual stat
	 *
	 * @param Application\DeskPRO\Entity\Stat $stat	The stat to process
	 */
	protected function processStat($stat)
	{
		
	}
	
}