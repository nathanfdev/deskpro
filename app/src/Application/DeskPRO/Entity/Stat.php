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
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Application\DeskPRO\App;

/**
 * Statistic
 *
 */
class Stat extends \Application\DeskPRO\Domain\DomainObject
{
	const FREQUENCY_DAILY   = "daily";
	const FREQUENCY_MONTHLY = "monthly";
	const FREQUENCY_YEARLY  = "yearly";

	const VARIATION_GOOD    = 'good';
	const VARIATION_BAD     = 'bad';
	const VARIATION_NEUTRAL = 'neutral';

	protected static $availableRunFrequencies = array(
		self::FREQUENCY_DAILY, self::FREQUENCY_MONTHLY, self::FREQUENCY_YEARLY
	);

	protected static $availableVariations = array(
		 self::VARIATION_GOOD, self::VARIATION_NEUTRAL,self::VARIATION_BAD
	);

	/**
	 * Lookup references for the grouping
	 *
	 * label: The display label for the grouping column
	 * table: The table the grouped label should come from
	 * display_column: The column to use in the table
	 */
	protected static $groupingReferences = array(
		'tickets.department_id'	        	=> array('label' => 'Department', 'table' => 'departments', 'display_column' => 'title'),
		'tickets.category_id'	        	=> array('label' => 'Category', 'table' => 'ticket_categories', 'display_column' => 'title'),
		'tickets.priority_id'	        	=> array('label' => 'Priority', 'table' => 'ticket_priorities', 'display_column' => 'title'),
		'tickets.workflow_id'	        	=> array('label' => 'Workflow', 'table' => 'ticket_workflows', 'display_column' => 'title'),
		'tickets.product_id'            	=> array('label' => 'Product', 'table' => 'products', 'display_column' => 'title'),
		'tickets.language_id'	        	=> array('label' => 'Language', 'table' => 'languages', 'display_column' => 'title'),
		'tickets.agent_id'              	=> array('label' => 'Agent', 'table' => 'people', 'display_column' => 'first_name'),
		'tickets.agent_team_id'	        	=> array('label' => 'Agent Team', 'table' => 'agent_teams', 'display_column' => 'name'),
		'tickets.date_created'          	=> array('label' => 'Created (Hour)', 'table' => null, 'display_column' => null),
		'tickets.organization_id'      		=> array('label' => 'Organization', 'table' => 'organizations', 'display_column' => 'name'),
		'tickets.person_id'             	=> array('label' => 'Person', 'table' => 'people', 'display_column' => 'first_name'),
		'labels_tickets.label'          	=> array('label' => 'Label', 'table' => null, 'display_column' => null),
		'person2usergroups.usergroup_id'	=> array('label' => 'User Group', 'table' => 'usergroups', 'display_column' => 'title'),
	);

	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * The stat title
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * The stat author
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_MAPPING\ManyToOne(targetEntity="Person", fetch="EAGER")
	 */
	protected $author;

	/**
	 * The stat this stat was cloned from (its parent)
	 *
	 * @var \Application\DeskPRO\Entity\Stat
	 * @ORM_MAPPING\ManyToOne(targetEntity="Stat")
	 * @ORM_MAPPING\JoinColumn(name="parent_stat_id", referencedColumnName="id")
	 */
	protected $parent_stat;

	/**
	 * The stat filter criteria
	 *
	 * @var string
	 */
	protected $criteria;

	/**
	 * The grouping type
	 *
	 * @var string
	 * @ORM_MAPPING\Column(name="grouping_ref", type="string", length=255)
	 */
	protected $grouping_ref;

	/**
	 * Stat concept class. The class responsible for getting the data for this stat
	 *
	 * @var string
	 * @ORM_MAPPING\Column(name="stat_concept_class", type="string", length=255)
	 */
	protected $stat_concept_class;

	/**
	 * The stat variation.
	 *
	 * @var string
	 * @ORM_MAPPING\Column(name="variation", type="string", length=7)
	 */
	protected $variation;

	/**
	 * Should we generate stats
	 *
	 * @var bool
	 * @ORM_MAPPING\Column(name="generate_stats", type="boolean")
	 */
	protected $generate_stats = false;

	/**
	 * Is the stat disabled
	 *
	 * @var bool
	 * @ORM_MAPPING\Column(name="disabled", type="boolean")
	 */
	protected $disabled = false;

	/**
	 * The frequency to run the stat
	 *
	 * @var string
	 * @ORM_MAPPING\Column(name="run_frequency", type="string", length=10)
	 */
	protected $run_frequency = "daily";

	/**
	 * The last run date
	 *
	 * @var \DateTime
	 * @ORM_MAPPING\Column(name="last_run", type="datetime", nullable=true)
	 */
	protected $last_run;

	/**
	 * The stat creation date
	 *
	 * @var \DateTime
	 */
	protected $date_created;

	/**
	 * Cache of the last data set retrieved. All operations performed on
	 * the stat such as getting the difference are performed on this data
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Flag to inidicate if data has been cached
	 *
	 * @var bool
	 */
	protected $_is_data_cached = false;

	/**
	 * The display unit
	 *
	 * @var string
	 */
	protected $_display_unit = '';

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the author name. Use the associated Person if one exists, otherwise
	 * its 'deskpro'
	 */
	public function getAuthorName()
	{
		if (!is_null($this->author)) {
			return $this->author->getDisplayName();
		}
		else {
			return 'Default';
		}
	}

	/**
	 * Get the Stat amount
	 *
	 * @return number The values summed
	 */
	public function getAmount()
	{
		if (0 === count($this->_data['ungrouped']['values'])) {
			return null;
		}

		return array_sum($this->_data['ungrouped']['values']);
	}

	/**
	 * Get the update period
	 *
	 * @return string The update period
	 */
	public function getPeriod()
	{
		return ucwords($this->getRunFrequency());
	}

	/**
	 * Is the stat clonable. At present all stats can be cloned
	 */
	public function isClonable()
	{
		return true;
	}

	/**
	 * Is the stat editable.  At present all stats can be edited
	 *
	 * @return bool
	 */
	public function isEditable()
	{
		return true;
	}

	/**
	 * Set disabled
	 *
	 * When disabling/enabling a stat we also need to disable/enable stat
	 * generation
	 */
	public function setDisabled($disabled)
	{
		$this->disabled = $disabled;
		$this->generate_stats = $disabled;
	}

	/**
	 * Calculate the Variance
	 *
	 * @param bool $as_percentage Get the variance as a percentage
	 * @return number The difference
	 */
	public function getDifference($as_percentage = false)
	{
		$previous_value = $this->getDataPoint(1, true, true);
		$current_value  = $this->getLastDataPoint();

		// No values, cannot calculate variations
		if (true === is_null($previous_value) || true === is_null($current_value)) {
			return null;
		}

		$difference = 0;
		if ($previous_value != 0) {
			$difference = ($current_value - $previous_value) / $previous_value;
		}

		return ($as_percentage) ? number_format($difference * 100, 2) : number_format($difference, 2);
	}

	/**
	 * Generate the trend points for the stat
	 *
	 * @param int $limit The number of trend points to get. If not specified
	 *                   will return all of them. Limit works from the end
	 *                   of the data set (optional)
	 * @return array The trend points
	 */
	public function getTrendPoints($limit = null)
	{
		if (0 === count($this->_data['ungrouped']['values'])) {
			return null;
		}

		$points = array_values($this->_data['ungrouped']['values']);

		if (true === is_null($limit)) {
			return $points;
		}
		else {
			return array_slice($points, ($limit*-1), $limit);
		}
	}

	/**
	 * Get the first data point
	 *
	 * @param bool $value True to return the vaule, false to return the label
	 */
	public function getFirstDataPoint($value = true)
	{
		return $this->getDataPoint(0, $value);
	}

	/**
	 * Get the last data point
	 *
	 * @param bool $value True to return the vaule, false to return the label
	 */
	public function getLastDataPoint($value = true)
	{
		return $this->getDataPoint(0, $value, true);
	}

	/**
	 * Get a data point by index
	 *
	 * @param int $index The index to return (starts at 0). Is $reverse is true
	 *                   index counts from end of array (ie, index 2 would
	 *                   get the 2nd from last element)
	 * @param bool $value True to return the vaule, false to return the label
	 * @param bool $reverse True to search from the end of the array
	 */
	public function getDataPoint($index, $value = true, $reverse = false)
	{
		if (0 === count($this->_data['ungrouped']['values'])) {
			return null;
		}

		if (true === $reverse) {
			$point = array_slice($this->_data['ungrouped']['values'], (($index+1) * -1), 1);
		}
		else {
			$point = array_slice($this->_data['ungrouped']['values'], $index, 1);
		}

		if ($value) {
			return $point[key($point)];
		}
		else {
			return date("F j", strtotime(key($point)));
		}
	}

	/**
	 * Gets the values of data from the end of the data set
	 *
	 * @param array $row The row to work with
	 * @param int $label The number of values to get
	 */
	public function getEndData($limit)
	{
		if (0 === count($this->_data['ungrouped']['values'])) {
			return null;
		}

		return array_slice($this->_data['ungrouped']['values'], ($limit * -1), $limit);
	}

	/**
	 * Get the number of data points based on the run frequency, eg, For
	 * daily reports show a week, for montly reports show a year
	 *
	 * @return int The number of data points to display
	 */
	public function getDefaultDataPointCount()
	{
		$data_points = 7;
		switch ($this->run_frequency) {
			case 'daily':
				// Get a week
				$data_points = 7;
				break;
			case 'monthly':
				// Get 12 months
				$data_points = 12;
				break;
			case 'yearly':
				// Get 10 years
				$data_points = 10;
				break;
		}

		return $data_points;
	}

	/**
	 * Get the number of data points based on the run frequency, eg, For
	 * daily reports show a week, for montly reports show a year
	 *
	 * @return int The number of data points to display
	 */
	public function getMaxDataPointCount()
	{
		$data_points = 7;
		switch ($this->run_frequency) {
			case 'daily':
				// Get a year
				$data_points = 60;
				break;
			case 'monthly':
				// Get 5 years
				$data_points = 60;
				break;
			case 'yearly':
				// Get 10 years
				$data_points = 10;
				break;
		}

		return $data_points;
	}

	/**
     * Get the run period length
     *
     * @return string
     */
	public function getRunPeriodLength()
	{
		$length = '';
		switch ($this->run_frequency) {
			case 'daily':
				$length = 'days';
				break;
			case 'monthly':
				$length = 'months';
				break;
			case 'yearly':
				$length = 'years';
				break;
		}

		return $length;
	}
	/**
	 * Get a StatValue by date
	 *
	 * @param \DateTime $date The DateTime to check
	 * @return StatValue The found StatValue entity or null
	 */
	public function getStatValueForDate(\DateTime $date)
	{
		$stat_value = null;

		$repo = App::getEntityRepository('DeskPRO:StatValue');

		switch ($this->run_frequency) {
			case 'daily':
				$stat_value = $repo->getForStatByDay($this->getId(), $date);
				break;
			case 'monthly':
				$stat_value = $repo->getForStatByMonth($this->getId(), $date);
				break;
			case 'yearly':
				$stat_value = $repo->getForStatByYear($this->getId(), $date);
				break;
			default:
				throw new \Exception("Unable to retrieve StatValue for run_frequency " . $this->run_frequency);
		}

		return $stat_value;
	}

	/**
	 * TODO: return the date the last full data processing happenend, for
	 * daily it will be the day before last_full_run, for monthly the
	 * month before last_full_run
	 */
	public function getLastFullRun()
	{
		return new \DateTime("2011-11-29 00:00:00");
	}

	public function setRunFrequency($run_frequency)
	{
		if (false === self::isValidRunFrequency($run_frequency)) {
			throw new \Exception("Unable to set run frequency to type $run_frequency. Supported types are " . join(", ", self::$availableRunFrequencies));
		}

		$this->run_frequency = $run_frequency;
	}

	public function setVariation($variaition)
	{
		if (false === self::isValidVariation($variaition)) {
			throw new \Exception("Unable to set variaition to type $variaition. Supported types are " . join(", ", self::$availableVariations));
		}

		$this->variation = $variaition;
	}

	public function getGroupingInformation()
	{
		return self::$groupingReferences[$this->getGroupingRef()];
	}

	/**
	 * Get the reference Id's for a StatValue
	 *
	 * @param array $stat_value_ids List of StatValue Ids to get reference for
	 * @return array()
	 */
	public function getGroupingReferenceIds($stat_value_ids)
	{
		return App::getEntityRepository('DeskPRO:StatValueGroup')
			->getReferenceIdsByStatValues($stat_value_ids);
	}

	/**
	 * Get the Reference loopup for the StatValue. Basically a Stat is mapped to
	 * a type of Grouping Data. We store the PK's for the linked grouping
	 * entity so we need a way to get the linked grouping entities back.
	 * This method will do this
	 *
	 * @param array $stat_value_ids List of StatValue Ids to get reference for
	 */
	public function getReferenceLookup($stat_value_ids)
	{
		$groupingInformation = $this->getGroupingInformation();

		$table 		= $groupingInformation['table'];
		$displayColumn 	= $groupingInformation['display_column'];

		$refefencesIds = array();
		// Only do the reference lookup if there is a table to look in.
		// Some group by fields store the label directly with in
		// StatValueGroup.grouping_ref field
		if (false === is_null($table)) {
			$refefencesIds  = $this->getGroupingReferenceIds($stat_value_ids);
		}

		$lookup = array();
		if (count($refefencesIds)) {
			$references = App::getDb()->fetchAll("
				SELECT id, $displayColumn
				FROM $table
				WHERE id IN (" . join(', ', $refefencesIds) . ")
			");

			foreach ($references as $reference) {
				$lookup[$reference['id']] = $reference[$displayColumn];
			}
		}

		return $lookup;
	}

	/**
	 * Get the grouping name. Useful for displaying
	 *
	 * @return string The name the stat is grouped by
	 */
	public function getGroupingName()
	{
		$groupingInformation = $this->getGroupingInformation();

		return $groupingInformation['label'];
	}

	/**
	 * Set the display unit
	 *
	 * @param string $display_unit The display unit
	 * @return string
	 */
	public function setDisplayUnits($display_unit)
	{
		$this->_display_unit = $display_unit;
	}

	/**
	 * Get the display unit
	 *
	 * @return string
	 */
	public function getDisplayUnits()
	{
		return $this->_display_unit;
	}

	/**
	 * Set the data
	 *
	 * @param
	 */
	public function setData($data)
	{
		$this->_data = $data;
	}

	/**
	 * Get the data for the Stat
	 *
	 * @param \DateTime $end_date The end date, we work backwards from this
	 * @param int $data_point_count The number of data points to retrieve
	 */
	public function getData(\DateTime $end_date, $data_point_count, $with_grouped = false)
	{
		$this->_data = array();
		// Get the data points we care about
		$data_points = $this->generateDataPoints($end_date, $data_point_count);

		$start_date  = new \DateTime($data_points[0] . '00:00:00');

		$stat_value_ids = array();

		// Get the StatValue's
		$stat_values = App::getEntityRepository('DeskPRO:StatValue')
				  ->getForStatRangeDate($this->getId(), $start_date, $end_date);

		// Transform the raw data - Set the default data points. We need
		// to do this incase there is missing data in the DB, ie we havent
		// generated stats as far as 5 years ago
		$values = array_fill_keys($data_points, null);
		foreach ($stat_values as $stat_value) {
			$values[date('Y-m-d', $stat_value['stat_unix'])] = $stat_value['value'];
			$stat_value_ids[] = $stat_value['id'];
		}

		$this->_data['ungrouped'] = array(
			'label'  => 'All',
			'values' => $values
		);

		if ($with_grouped) {
			$this->_data['grouped']   = $this->getGroupedData($data_points, $stat_value_ids);
		}

		$this->_is_data_cached = true;
		return $this->_data;
	}

	/**
	 * Get the grouped data (StatValueGroup) for the Stat
	 *
	 * @param array $data_points List of data points
	 * @param array $stat_value_ids List of StatValue Ids to get data for
	 */
	protected function getGroupedData($data_points, $stat_value_ids)
	{
		// Get the lookup data for the labels
		$lookup = $this->getReferenceLookup($stat_value_ids);

		$data = array();

		foreach ($stat_value_ids as $stat_value_id) {

			// Get the StatValueGroups
			$raw = App::getEntityRepository('DeskPRO:StatValueGroup')
				   ->getForStatValue($stat_value_id);

			// Transform the raw data
			foreach ($raw as $raw_row) {
				if (false === isset($data[$raw_row['grouping_ref']])) {
					$label = '';
					// Get the label, check the grouping_ref is set, could
					// be a NULL reference, or may not require lookup
					if (isset($lookup[$raw_row['grouping_ref']])) {
						$label = $lookup[$raw_row['grouping_ref']];
					}
					// The grouping_ref its self is the label, there
					// is no lookup required
					elseif (strlen($raw_row['grouping_ref']) > 0) {
						$label = $raw_row['grouping_ref'];
					}
					else {
						// Get the grouping name
						$label = 'No ' . $this->getGroupingName();
					}

					// Set the default data points. We need
					// to do this incase there is missing data in the DB, ie we havent
					// generated stats as far as 5 years ago
					$values = array_fill_keys($data_points, null);

					$data[$raw_row['grouping_ref']] = array(
						'label'  => $label,
						'values' => $values,
					);
				}

				$data[$raw_row['grouping_ref']]['values'][date('Y-m-d', $raw_row['stat_unix'])] = $raw_row['value'];
			}
		}

		return $data;
	}

	/**
	 * Calculate the values for the data points
	 *
	 * @param \Datetime $end_date The end date
	 * @param int $data_point_count The number of data points to get
	 * @return array
	 */
	protected function generateDataPoints(\DateTime $end_date, $data_point_count)
	{
		$data_points = array();

		$unix = $end_date->format('U');

		for ($i = ($data_point_count - 1); $i >= 0; $i--) {
			switch ($this->getRunFrequency()) {
				case 'daily':
					$data_point = date('Y-m-d', strtotime("-$i days", $unix));
					break;
				case 'monthly':
					$data_point = date('Y-m-d', strtotime("-$i months", $unix));
					break;
				case 'yearly':
					$data_point = date('Y-m-d', strtotime("-$i years", $unix));
					break;
				default:
					throw new \Exception("Unsupported run frequency " . $this->getRunFrequency());
					break;
			}

			$data_points[] = $data_point;
		}

		return $data_points;
	}

	/**
	 * Get the latest stat values
	 *
	 * @param int $data_point_count The number of values to get
	 */
	public function getLatestStatValues($data_point_count)
	{
		$end_date = new \DateTime();
		$stat_values = $this->getData($end_date, $data_point_count);

		return $stat_values;
	}

	/**
	 * Checks if a run requency is valid
	 *
	 * @param string $run_frequency The run frequency to check
	 * @return bool
	 */
	public static function isValidRunFrequency($run_frequency)
	{
		return (in_array($run_frequency, self::$availableRunFrequencies)) ? true : false;
	}

	/**
	 * Get the available run frequencies
	 *
	 * @return array
	 */
	public static function getAvailableRunFrequencies()
	{
		return self::$availableRunFrequencies;
	}

	/**
	 * Checks if a variaition is valid
	 *
	 * @param string $variation The variaition to check
	 * @return bool
	 */
	public static function isValidVariation($variation)
	{
		return (in_array($variation, self::$availableVariations)) ? true : false;
	}

	/**
	 * Get the available variations
	 *
	 * @return array
	 */
	public static function getAvailableVariations()
	{
		return self::$availableVariations;
	}

	/**
	 * Get the available grouping references
	 *
	 * @return array
	 */
	public static function getGroupingReferences()
	{
		return self::$groupingReferences;
	}

	/**
	 * Get the available grouping references in display format
	 *
	 * @return array
	 */
	public static function getGroupingReferencesDisplay()
	{
		$results = array();

		foreach (self::$groupingReferences as $k=>$grouping) {
			$results[$k] = $grouping['label'];
		}

		return $results;
	}

	public function getFormatter()
	{
		$concept_class = $this->getStatConceptClass();

		return $concept_class::getFormatter();
	}

	/**
	 * Format the data using the set formatter
	 *
	 * @param mixed $data The data to format
	 * @param array $options Various formatting options
	 * @return mixed The formatted data
	 */
	public function formatData($data, $unit = '', array $options = array())
	{
		if (strlen($unit) === 0) {
			$unit = $this->_display_unit;
		}

		if (false === is_null($this->getFormatter())) {
			$data = $this->getFormatter()->formatData($data, $unit, $options);
		}

		return $data;
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Stat';
		$metadata->setPrimaryTable(array( 'name' => 'stat', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'title', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'title', ));
		$metadata->mapField(array( 'fieldName' => 'criteria', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'criteria', ));
		$metadata->mapField(array( 'fieldName' => 'grouping_ref', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'grouping_ref', ));
		$metadata->mapField(array( 'fieldName' => 'stat_concept_class', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'stat_concept_class', ));
		$metadata->mapField(array( 'fieldName' => 'variation', 'type' => 'string', 'length' => 7, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'variation', ));
		$metadata->mapField(array( 'fieldName' => 'generate_stats', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'generate_stats', ));
		$metadata->mapField(array( 'fieldName' => 'disabled', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'disabled', ));
		$metadata->mapField(array( 'fieldName' => 'run_frequency', 'type' => 'string', 'length' => 10, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'run_frequency', ));
		$metadata->mapField(array( 'fieldName' => 'last_run', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'last_run', ));
		$metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'date_created', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapManyToOne(array( 'fieldName' => 'author', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'author_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => NULL, 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapManyToOne(array( 'fieldName' => 'parent_stat', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Stat', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'parent_stat_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => NULL, 'columnDefinition' => NULL, ), ),  ));
	}
}
