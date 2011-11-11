<?php

namespace Application\ReportBundle\Stat\Base;

use Application\DeskPRO\App;

abstract class AbstractStat implements StatInterface
{
	protected $available_grouping = array();

	protected $trendQueries = array();

	protected $results = array();

	protected $db;

	public function __construct()
	{
		$this->db = App::getDb();
	}

	public function getStats()
	{
		$results = array('ungrouped', 'grouped');

		$results['ungrouped'] = $this->executeQueries();

		$results['grouped'] = $this->executeQueries(true);

		return $results;
	}

	/**
	 * Execute the trend concept query
	 */
	protected function executeQueries($with_grouping = false)
	{
		foreach ($this->trendQueries as $query) {
			$results[] = $this->db->fetchAll($query);
		}

		return $this->processResults();
	}

	public function getAvailableGrouping()
	{
		return $this->available_grouping;
	}

	public function removeGrouping($field)
	{
		// Remove field from grouping
	}

	public function addGroup($field)
	{
		// Add field to grouping
	}

	/**
	 * Get the Dates from $start for $num_days
	 *
	 * @param int $start Start time in unix
	 * @param int $num_days Number of days to get
	 *
	 * @return array The dates in unix format
	 */
	public static function getDatesPast($start, $num_days)
	{
		$dates = array();
		$startUnix = $start - (\Orb\Util\Dates::SECS_DAY * $num_days);
		for ($unix = $startUnix; $unix <= time(); $unix+=\Orb\Util\Dates::SECS_DAY) {
			$dates[] = $unix;
		}

		return $dates;
	}
}