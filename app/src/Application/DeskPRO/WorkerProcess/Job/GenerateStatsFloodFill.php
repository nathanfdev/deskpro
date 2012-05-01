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
use Application\DeskPRO\Entity\Stat;
use Application\DeskPRO\Entity\StatValue;
use Application\DeskPRO\Entity\StatValueGroup;

/**
 * Generate stats for the reporting system. This is for debugging purposes only
 * and should be removed or integrated into GenerateStat Job
 */
class GenerateStatsFloodFill extends AbstractJob
{
	const DEFAULT_INTERVAL = 86400; // Daily

	protected $date_time;

	protected $orm;

	/**
	 * Number of data points to flood fill for
	 *
	 * @var int
	 */
	protected $flood_fill_count = 50;

	public function run()
	{
		$end_date = new \DateTime();

		$this->orm  = App::getOrm();

		// Get the available fun frequencies
		$run_frequencies = Stat::getAvailableRunFrequencies();

		foreach ($run_frequencies as $run_frequency) {
			for ($i = 0; $i < $this->flood_fill_count; $i++) {

				$this->date_time = $this->getDate($run_frequency, $end_date, $i);

				// Generate the run frequency method to execute
				$method = 'get' . ucwords($run_frequency) . 'StatIdsRequiringUpdate';

				$stat_ids 	= App::getEntityRepository('DeskPRO:Stat')
							->$method(\DateTime::createFromFormat('U',  time()+self::DEFAULT_INTERVAL));

				$count_stats 	= count($stat_ids);

				$msg = '[' . ucwords($run_frequency) . "] Processing {$count_stats} stats";
				$this->logStatus($msg);

				if (count($stat_ids)) {
					$this->processStatIds($stat_ids);
				}
			}
		}
	}

	/**
	 * Get a previous date based on the $run_frequency using the $end_date as
	 * the reference point. If $run_frequency is 'daily' and $points is 5
	 * we get the day 5 days previous to $end_date
	 */
	protected function getDate($run_frequency, $end_date, $points)
	{
		$unix = $end_date->format('U');

		switch ($run_frequency) {
			case 'hourly':
				$new_unix = date('U', strtotime("-$points hours", $unix));
				break;
			case 'daily':
				$new_unix = date('U', strtotime("-$points days", $unix));
				break;
			case 'monthly':
				$new_unix = date('U', strtotime("-$points months", $unix));
				break;
			case 'yearly':
				$new_unix = date('U', strtotime("-$points years", $unix));
				break;
		}

		return \DateTime::createFromFormat('U', $new_unix);
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

		$stat_concept = new $stat_concept_class($stat, $stat->getLastFullRun());
		$stat_concept->addGrouping($stat->getGroupingRef());
		$values = $stat_concept->getStats();

		// Check if existing StatValue is set for period
		$stat_value = $stat->getStatValueForDate($this->date_time);
		if (!$stat_value) {
			// Create a new one
			$stat_value = new StatValue();
			$stat_value->setStat($stat);
		}
		$stat_value->setValue($values['ungrouped'] + rand(0, 30));
		$stat_value->setStatUnix($this->date_time->format('U'));
		$this->orm->persist($stat_value);

		// Store the grouped values
		foreach ($values['grouped'] as $grouped) {
			// Check if existing StatValueGroup is set for period and reference
			$stat_value_group = $stat_value->getStatValueGroupForDate(
				$this->date_time,
				$grouped['grouping_ref'],
				$stat->getRunFrequency()
			);

			if (!$stat_value_group) {
				$stat_value_group = new StatValueGroup();
				$stat_value_group->setStatValue($stat_value);
			}
			$stat_value_group->setValue($grouped['value'] + rand(0, 20));
			$stat_value_group->setGroupingRef($grouped['grouping_ref']);
			$stat_value_group->setStatUnix($this->date_time->format('U'));
			$this->orm->persist($stat_value_group);
		}

		// Update the last run
		$stat->setLastRun(new \DateTime());
		$this->orm->persist($stat);

		// Flush - need to batch this
		$this->orm->flush();
	}
}
