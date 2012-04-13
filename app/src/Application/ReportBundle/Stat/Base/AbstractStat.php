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
*/

namespace Application\ReportBundle\Stat\Base;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Stat;
use Application\ReportBundle\Stat\Searcher\ReportSearchInterface;

abstract class AbstractStat implements StatInterface
{
	/**
	 * The Stat entity to represent
	 *
	 * @var Stat
	 */
	protected $stat = null;

	/**
	 * The searcher to use to build the base query
	 *
	 * @vas ReportSearchInterface
	 */
	protected $searcher = null;

	/**
	 * List of available grouping the stat has
	 *
	 * @var array
	 */
	protected $available_groupings = array();

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
	 * @var \DateTime
	 */
	protected $last_stat_date = null;

	/**
	 * DB instance
	 */
	protected $db;

	/**
	 * @var \Application\DeskPRO\Log\Logger
	 */
	protected $logger = null;

	public function __construct(Stat $stat, \DateTime $last_stat_date)
	{
		$this->stat = $stat;
		$this->last_stat_date = $last_stat_date;

		$this->db = App::getDb();

		$this->results = array('ungrouped' => array(), 'grouped' => array());

		$this->init();
	}

	/**
	 * @param \Application\DeskPRO\Log\Logger $logger
	 */
	public function setLogger(\Application\DeskPRO\Log\Logger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Set the searcher
	 */
	public function setSearcher(ReportSearchInterface $searcher)
	{
		$this->searcher = $searcher;
	}

	/**
	 * Get the searcher
	 */
	public function getSearcher()
	{
		return $this->searcher;
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

		$this->results['ungrouped'] = $this->processUngroupedResults($this->results['ungrouped']);
		$this->results['grouped']   = $this->processGroupedResults($this->results['grouped']);

		return $this->results;
	}

	/**
	 * Create a QueryBuilder object ready to be used. The QueryBuilder
	 * is initialised with the Searcher query first
	 *
	 * @return QueryBuilder
	 */
	public function createQuery()
	{
		if (is_null($this->searcher)) {
			throw new \Exception("You must supply a searcher");
		}

		// Process the search terms
		foreach ($this->stat->getCriteria() as $term) {
			$this->searcher->addTerm($term['type'], $term['op'], $term['options']);
		}

		// Create the QueryBuilder and apply the Searcher query to it
		$query = new QueryBuilder($this->db);
		$this->searcher->buildQuery($query);

		return $query;
	}

	protected function addQuery($identifier, QueryBuilder $query)
	{
		$this->trend_queries[$identifier] = $query;
	}

	/**
	 * Execute the trend concept query
	 */
	protected function executeQueries($with_grouping = false)
	{
		foreach ($this->trend_queries as $identifier=>$query) {
			// Need to build the actual queries here and apply grouping if its needed
			$executeQuery = $query;

			if ($with_grouping) {
				$this->applyGroupByToQuery($executeQuery);
				if ($this->logger) $this->logger->log("=> Query: " . $query->getSql(), 'DEBUG');
				$this->results['grouped'][$identifier] = $executeQuery->execute()->fetchAll(\PDO::FETCH_ASSOC);
			}
			else {
				if ($this->logger) $this->logger->log("=> Query: " . $query->getSql(), 'DEBUG');
				$this->results['ungrouped'][$identifier] = $executeQuery->execute()->fetchAll(\PDO::FETCH_ASSOC);
			}
		}
	}

	/**
	 * Apply the group by to the query. We may need to do some additional
	 * processing such as ensuring the group by field is being selected,
	 * and limiting the number of results. If any group by fields require
	 * joining to other tables, override this method in the child class.
	 *
	 * @param QueryBuilder $query
	 */
	protected function applyGroupByToQuery(QueryBuilder $query)
	{

		foreach ($this->grouping as $groupField) {
			// Check if we are allowed to group by this field
			if (false === $this->isGroupingFieldAllowed($groupField)) {
				throw new \Exception("Cannot group by field $groupField. Field is not in the available_groupings list.");
			}

			$groupingInfo = $this->getAvailableGrouping($groupField);

			list($table, $field) = explode('.', $groupField);

			$alias = $query->getTableAlias($table);
			$aliasedGroupField = $alias . '.' . $field;
			$formattedGroupField = $aliasedGroupField;
			if (isset($groupingInfo['format'])) {
				$formattedGroupField = $groupingInfo['format'];
			}

			// We need to return the select group by field
			if (false === $query->isFieldSelected($aliasedGroupField)) {
				$query->addSelect($formattedGroupField . ' AS ' . str_replace('.', '_', $groupField));
			}

			$query->addGroupBy($formattedGroupField);

			// Check for limit
			$groupingInfo = $this->getAvailableGrouping($groupField);
			if (false !== $groupingInfo['limit']) {
				$query->setMaxResults($groupingInfo['limit']);
			}
		}
	}

	/**
	 * Checks a group by field is allowed on the query
	 *
	 * @param string $field The field to check, format table.field_name
	 * @return bool
	 */
	public function isGroupingFieldAllowed($field)
	{
		if (isset($this->available_groupings[$field])) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Get the grouping fields
	 *
	 * @return string
	 */
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
	public function getAvailableGroupings()
	{
		return $this->available_groupings;
	}

	/**
	 * Get the available groupings
	 */
	public function getAvailableGrouping($group_by)
	{
		if (isset($this->available_groupings[$group_by])) {
			return $this->available_groupings[$group_by];
		}
		else {
			return null;
		}
	}

	/**
	 * Remove a field from the available groupings
	 */
	public function removeAvailableGrouping($field_value)
	{
		// Remove field from grouping
		if (isset($this->available_groupings[$field_value])) {
			unset($this->available_groupings[$field_value]);
		}
	}

	/**
	 * Adds a field to the available grouping
	 */
	public function addAvailableGroup($field_value, $field_name)
	{
		// Add field to grouping
		if (false === isset($this->available_groupings[$field_value])) {
			$this->available_groupings[$field_value] = $field_name;
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