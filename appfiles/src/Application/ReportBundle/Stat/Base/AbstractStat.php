<?php

namespace Application\ReportBundle\Stat\Base;

use Application\DeskPRO\App;

abstract class AbstractStat implements StatInterface
{
	/**
	 * List of available grouping the stat has
	 *
	 * @var array
	 */
	protected $available_grouping = array();

	/**
	 * The queries to execute
	 *
	 * @var array
	 */
	protected $trend_queries = array();

	/**
	 * The fields the query should group by
	 */
	protected $grouping = array();

	/**
	 * The raw results from the $trend_queries
	 *
	 * @var array
	 */
	protected $results = array();

	/**
	 * The last stat date. Useful if you need to see what has happened
	 * sine the last time the stat collected data
	 *
	 * @var \DaeTime
	 */
	protected $last_stat_date = null;

	/**
	 * DB instance
	 */
	protected $db;

	public function __construct(\DateTime $last_stat_date)
	{
		$this->last_stat_date = $last_stat_date;

		$this->db = App::getDb();

		$this->results = array('ungrouped' => array(), 'grouped' => array());

		$this->init();
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

	/**
	 * Create a QueryBuilder object ready to be used
	 *
	 * @return QueryBuilder
	 */
	public function createQuery()
	{
		return new QueryBuilder($this->db);
	}

	protected function addQuery(QueryBuilder $query)
	{
		$this->trend_queries[] = $query;
	}

	/**
	 * Execute the trend concept query
	 */
	protected function executeQueries($with_grouping = false)
	{
		foreach ($this->trend_queries as $query) {
			// Need to build the actual queries here and apply grouping if its needed
			$executeQuery = $query;

			if ($with_grouping) {
				$executeQuery = $this->applyGroupByToQuery($executeQuery);
				$this->results['grouped'] = $this->processGroupedResults($executeQuery->execute()->fetchAll(\PDO::FETCH_ASSOC));
			}
			else {
				$this->results['ungrouped'] = $this->processUngroupedResults($executeQuery->execute()->fetchAll(\PDO::FETCH_ASSOC));
			}
		}

	}

	protected function applyGroupByToQuery(QueryBuilder $query)
	{
		// We need to return the select group by field
		foreach ($this->grouping as $groupField) {
			list($table, $field) = explode('.', $groupField);

			$alias = $query->getTableAlias($table);
			$aliasedGroupField = $alias . '.' . $field;

			if (false === $query->isFieldSelected($aliasedGroupField)) {
				$query->addSelect($aliasedGroupField . ' AS ' . str_replace('.', '_', $groupField));
			}

			$query->addGroupBy($aliasedGroupField);
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
	public function removeAvailableGrouping($field_value)
	{
		// Remove field from grouping
		if (isset($this->available_grouping[$field_value])) {
			unset($this->available_grouping[$field_value]);
		}
	}

	/**
	 * Adds a field to the available grouping
	 */
	public function addAvailableGroup($field_value, $field_name)
	{
		// Add field to grouping
		if (false === isset($this->available_grouping[$field_value])) {
			$this->available_grouping[$field_value] = $field_name;
		}
	}

	/**
	 * Adds multiple available groups
	 *
	 * @param array $groups List of groups to add
	 * 	<code>
	 *      	array('field_id' => 'Field Name');
	 * 	</code>
	 */
	public function addAvailableGroups($groups)
	{
		foreach ($groups as $field_value=>$field_name) {
			$this->addAvailableGroup($field_value, $field_name);
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

	/**
	 * Get the data formatter. Override in your child classes
	 *
	 * @return FormatterInterface
	 */
	public static function getFormatter()
	{
		return null;
	}
}