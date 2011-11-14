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
use Application\DeskPRO\Entity\StatValueGroup;

/**
 * Generate stats for the reporting system
 */
class GenerateStats extends AbstractJob
{
	const DEFAULT_INTERVAL = 86400; // Daily

	protected $date_time;

	protected $orm;

	public function run()
	{
		$this->date_time = new \DateTime();
		$this->date_time = \DateTime::createFromFormat('U', time()+self::DEFAULT_INTERVAL);

		$this->orm  = App::getOrm();

		// Get the available fun frequencies
		$run_frequencies = Stat::getAvailableRunFrequencies();

		foreach ($run_frequencies as $run_frequency) {
			// Generate the run frequency method to execute
			$method = 'get' . ucwords($run_frequency) . 'StatIdsRequiringUpdate';

			$stat_ids 	= App::getEntityRepository('DeskPRO:Stat')
						->$method($this->date_time);

			$count_stats 	= count($stat_ids);

			$msg = '[' . ucwords($run_frequency) . "] Processing {$count_stats} stats";
			$this->logStatus($msg);

			if (count($stat_ids)) {
				$this->processStatIds($stat_ids);
			}
		}
	}

	/**
	 * Process a list of stats
	 *
	 * @param array $stat_ids  List of stat ids to process
	 */
	protected function processStatIds($stat_ids)
	{
		// Get the all the stats
		$stats = App::getEntityRepository('DeskPRO:Stat')
				->getByIds($stat_ids);

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
			$stat_value_group = new StatValueGroup();
			$stat_value_group->setStatValue($stat_value);
			$stat_value_group->setValue($grouped['value']);
			$stat_value_group->setGroupingId($grouped['grouping_id']);
			$this->orm->persist($stat_value_group);
		}

		// Update the last run
		$stat->setLastRun(new \DateTime());
		$this->orm->persist($stat);

		// Flush - need to batch this
		$this->orm->flush();
	}

}