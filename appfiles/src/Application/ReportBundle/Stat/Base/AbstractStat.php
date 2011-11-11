<?php

namespace Application\ReportBundle\Stat\Base;

use Application\DeskPRO\App;

abstract class AbstractStat implements StatInterface
{
	protected $available_grouping = array();

	protected $trendQueries = array();

	/**
	 * The fields the query should group by
	 */
	protected $grouping = array();

	protected $results = array();

	protected $db;

	public function __construct()
	{
		$this->db = App::getDb();
	}

	/**
	 * Get the actual stats. Runs grouped and ungrouped queries
	 */
	public function getStats()
	{
		$results = array('ungrouped', 'grouped');

		$results['ungrouped'] = $this->executeQueries();

		if ($this->hasGrouping()) {
			$results['grouped'] = $this->executeQueries(true);
		}

		return $results;
	}

	/**
	 * Execute the trend concept query
	 */
	protected function executeQueries($with_grouping = false)
	{
		$this->buildConceptQueries();

		foreach ($this->trendQueries as $query) {
			// Need to build the actual queries here and apply grouping if its needed
			$this->results[] = $this->db->fetchAll($query);
		}

		return $this->processResults();
	}

	/**
	 * Adds a field to be grouped by
	 */
	public function addGrouping($field)
	{
		// Add field to grouping
		if (false === in_array($field, $this->grouping)) {
			$this->grouping[] = $field;
		}
	}

	/**
	 * Is there any grouping set
	 */
	public function hasGrouping()
	{
		return (count($this->grouping)) ? true : false;
	}

	/**
	 * Get the available groupings
	 */
	public function getAvailableGrouping()
	{
		return $this->available_grouping;
	}

	/**
	 * Remove a field from the available groupings
	 */
	public function removeAvailableGrouping($field)
	{
		// Remove field from grouping
		if (in_array($field, $this->available_grouping)) {
			$key = array_search($field, $this->available_grouping);
			unset($this->available_grouping[$key]);
		}
	}

	/**
	 * Adds a field to the available grouping
	 */
	public function addAvailableGroup($field)
	{
		// Add field to grouping
		if (false === in_array($field, $this->available_grouping)) {
			$this->available_grouping[] = $field;
		}
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