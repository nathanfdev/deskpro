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
use Application\DeskPRO\Entity\StatValue;

/**
 * Generate stats for the reporting system
 */
class GenerateStats extends AbstractJob
{
	const DEFAULT_INTERVAL = 86400; // Daily

	protected $time;

	protected $orm;

	public function run()
	{
		$this->time = time();

		$this->orm  = App::getOrm();

		// Get the stats
		$stats 		= App::getEntityRepository('DeskPRO:Stat')
				     ->getStatsRequiringUpdate($this->time);

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
			$this->processStat($stat);
		}
	}

	/**
	 * Process an individual stat
	 *
	 * @param Application\DeskPRO\Entity\Stat $stat	The stat to process
	 */
	protected function processStat($stat)
	{
		$stat_concept_class = $stat->getStatConceptClass();

		$stat_concept = new $stat_concept_class();
		$stat_concept->addGrouping($stat->getGroupingRef());
		$values = $stat_concept->getStats($this->time);

		// Store the stat value
		$stat_value = new StatValue();
		$stat_value->setStat($stat);
		$stat_value->setValue($values['ungrouped']);
		$this->orm->persist($stat_value);

		// Store the grouped values
		foreach ($values['grouped'] as $grouped) {
			$value = $grouped['value'];

			$stat_value_group = new StatValueGroup();
			$stat_value_group->setStatValue($stat_value);
			$stat_value_group->setValue($value);
			$this->orm->persist($stat_value_group);
		}

		// Update the last run
		$stat->setLastRun(new \DateTime());
		$this->orm->persist($stat);

		// Flush - need to batch this
		$this->orm->flush();
	}

}