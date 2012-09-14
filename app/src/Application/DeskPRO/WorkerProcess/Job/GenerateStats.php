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
 * Generate stats for the reporting system
 */
class GenerateStats extends AbstractJob
{
	const DEFAULT_INTERVAL = 3600; // Hourly

	protected $date_time;

	protected $orm;

	public function run()
	{
		return;

		$this->date_time = new \DateTime();
		$this->date_time = \DateTime::createFromFormat('U',  time()+self::DEFAULT_INTERVAL);

		$this->orm  = App::getOrm();

		// Get the available fun frequencies
		$run_frequencies = Stat::getAvailableRunFrequencies();

		foreach ($run_frequencies as $run_frequency) {
			// Generate the run frequency method to execute
			$method = 'get' . ucwords($run_frequency) . 'StatIdsRequiringUpdate';

			$stat_ids = App::getEntityRepository('DeskPRO:Stat')->$method($this->date_time);

			$count_stats = count($stat_ids);

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
		$stats = App::getEntityRepository('DeskPRO:Stat')->getByIds($stat_ids);

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
		$this->logStatus("Processing {$stat->id} {$stat->title}");

		$stat_concept_class = $stat->getStatConceptClass();

		/** @var $stat_concept \Application\ReportBundle\Stat\Base\AbstractStat */
		$stat_concept = new $stat_concept_class($stat, $stat->getLastFullRun());

		$stat_concept->setLogger($this->logger);
		$stat_concept->addGrouping($stat->getGroupingRef());
		$values = $stat_concept->getStats($this->date_time);

		// Check if existing StatValue is set for period
		$stat_value = $stat->getStatValueForDate(new \DateTime());
		if (!$stat_value) {
			// Create a new one
			$stat_value = new StatValue();
			$stat_value->setStat($stat);
		}
		$stat_value->setValue($values['ungrouped']);
		$stat_value->setStatUnix(time());
		$this->orm->persist($stat_value);

		// Store the grouped values
		foreach ($values['grouped'] as $grouped) {
			// Check if existing StatValueGroup is set for period and reference
			$stat_value_group = $stat_value->getStatValueGroupForDate(
				new \DateTime(),
				$grouped['grouping_ref'],
				$stat->getRunFrequency()
			);

			if (!$stat_value_group) {
				$stat_value_group = new StatValueGroup();
				$stat_value_group->setStatValue($stat_value);
			}
			$stat_value_group->setValue($grouped['value']);
			$stat_value_group->setGroupingRef($grouped['grouping_ref']);
			$stat_value_group->setStatUnix(time());
			$this->orm->persist($stat_value_group);
		}

		$this->logStatus("=> Value: {$values['ungrouped']} with " . count($values['grouped']) . " groups");

		// Update the last run
		$stat->setLastRun(new \DateTime());
		$this->orm->persist($stat);

		// Flush - need to batch this
		$this->orm->flush();
	}
}
