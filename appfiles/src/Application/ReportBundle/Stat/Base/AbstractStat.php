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

		$this->results = array('ungrouped' => array(), 'grouped' => array());
	}

	/**
	 * Get the actual stats. Runs grouped and ungrouped queries
	 */
	public function getStats()
	{
		$this->buildConceptQueries();

		$this->executeQueries();

		if ($this->hasGrouping()) {
			$this->executeQueries(true);
		}

		return $this->results;
	}

	protected function addQuery(Query $query)
	{
		$this->trendQueries[] = $query;
	}

	/**
	 * Execute the trend concept query
	 */
	protected function executeQueries($with_grouping = false)
	{
		foreach ($this->trendQueries as $query) {
			// Need to build the actual queries here and apply grouping if its needed
			$executeQuery = $query;

			if ($with_grouping) {
				$executeQuery = $this->applyGroupByToQuery($executeQuery);

				$this->results['grouped'] = $this->processGroupedResults($this->db->fetchAll($executeQuery->getSql()));
			}
			else {
				$this->results['ungrouped'] = $this->processUngroupedResults($this->db->fetchAll($executeQuery->getSql()));
			}
		}

	}

	protected function applyGroupByToQuery(QueryBuilder $query)
	{
		// We need to return the select group by field
		foreach ($this->grouping as $groupField) {
			if (false === $query->isFieldSelected($groupField)) {
				$query->addSelect($groupField . ' AS ' . str_replace('.', '_', $groupField));
			}

			$query->addGroupBy($groupField);
		}

		return $query;
	}

	protected function getGroupingFields()
	{
		return join(", ", $this->grouping);
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