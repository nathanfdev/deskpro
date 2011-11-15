<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;
use Application\DeskPRO\App;

/**
 * Statistic
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Stat")
 * @ORM_Mapping\Table(name="stat")
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
		self::VARIATION_BAD, self::VARIATION_NEUTRAL, self::VARIATION_GOOD
	);

	/**
	 * Lookup references for the grouping
	 */
	protected static $groupingReferences = array(
		'tickets.agent_id' => array('table' => 'people', 'display_column' => 'first_name'),
	);

	/**
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The stat title
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * The stat author
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_MAPPING\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="author_id", referencedColumnName="id")
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
	 * @ORM_Mapping\Column(name="criteria", type="array")
	 */
	protected $criteria;

	/**
	 * The grouping type
	 *
	 * @var string
	 * @ORM_MAPPING\Column(name="grouping_ref", type="string", length="255")
	 */
	protected $grouping_ref;

	/**
	 * Stat concept class. The class responsible for getting the data for this stat
	 *
	 * @var string
	 * @ORM_MAPPING\Column(name="stat_concept_class", type="string", length="500")
	 */
	protected $stat_concept_class;

	/**
	 * The stat variation.
	 *
	 * @var string
	 * @ORM_MAPPING\Column(name="variation", type="string", length="7")
	 */
	protected $variation;

	/**
	 * Is the stat starred
	 *
	 * @var boolean
	 * @ORM_MAPPING\Column(name="starred", type="boolean")
	 */
	protected $starred = false;

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
	 * @ORM_MAPPING\Column(name="run_frequency", type="string", length="10")
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
	 * @ORM_Mapping\Column(name="date_created", type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
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
			return 'deskpro';
		}
	}

	/**
	 * Get the Stat amount
	 *
	 * TODO: currently random placeholder
	 */
	public function getAmount()
	{
		return rand(0, 100);
	}

	/**
	 * Get the Stat variation
	 *
	 * TODO: currently random placeholder
	 */
	public function getVariation()
	{
		return rand(-5, 5);
	}

	/**
	 * Generate the trend points for the stat
	 *
	 * TODO: currently just generate some random placeholer data
	 */
	public function getTrendPoints()
	{
		$trendPoints = range(0, 10);
		shuffle($trendPoints);

		return $trendPoints;
	}

	/**
	 * Get the number of data points based on the run frequency
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
				$stat_value = $repo->getForStatByDay($this->id, $date);
				break;
			case 'monthly':
				$stat_value = $repo->getForStatByMonth($this->id, $date);
				break;
			case 'yearly':
				$stat_value = $repo->getForStatByYear($this->id, $date);
				break;
			default:
				throw new \Exception("Unable to retrieve StatValue for run_frequency " . $this->run_frequency);
		}

		return $stat_value;
	}

	/**
	 * Get a value for a date
	 *
	 * @param int $unix Unix timestamp to get value for
	 */
	public function getValueForDate($unix)
	{

	}

	/**
	 * Get the reference Id's for a stat. Optionaly limit to a date and count
	 *
	 * @param \DateTime $end_date The end date to limit to (optional)
	 * @param int $limit The number of reference Id's to retrieve (optional)
	 * @return array()
	 */
	public function getGroupingReferenceIds(\DateTime $end_date = null, $limit = null)
	{
		return App::getEntityRepository('DeskPRO:StatValueGroup')
			->getReferenceIdsByStat($this->getId(), $end_date, $limit);
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
	 * Get the Reference loopup for the Stat. Basically a Stat is mapped to
	 * a type of Grouping Data. We store the PK's for the linked grouping
	 * entity so we need a way to get the linked grouping entities back.
	 * This method will do this
	 *
	 * @param \DateTime $end_date The end date, we work backwards from this
	 * @param int $data_point_count The number of data points to retrieve
	 */
	public function getReferenceLookup(\DateTime $end_date, $data_point_count = null)
	{
		// If a data point count is not set, use the Stat default
		if (true === is_null($data_point_count)) {
			$data_point_count = $this->getDefaultDataPointCount();
		}

		$groupingInformation = $this->getGroupingInformation();

		$table 		= $groupingInformation['table'];
		$displayColumn 	= $groupingInformation['display_column'];

		$refefencesIds  = $this->getGroupingReferenceIds($end_date, $data_point_count);

		$references = App::getDb()->fetchAll("
			SELECT id, $displayColumn
			FROM $table
			WHERE id IN (" . join(', ', $refefencesIds) . ")
		");

		$lookup = array();
		foreach ($references as $reference) {
			$lookup[$reference['id']] = $reference[$displayColumn];
		}

		return $lookup;
	}

	/**
	 * Get the data for the Stat
	 *
	 * @param \DateTime $end_date The end date, we work backwards from this
	 * @param int $data_point_count The number of data points to retrieve
	 */
	public function getData(\DateTime $end_date, $data_point_count, $with_grouped = false)
	{
		// Get the StatValue's
		$data = $this->getUngroupedData($end_date, $data_point_count);

		if ($with_grouped) {
			$data['ungrouped'] = $data;
			$data['grouped']   = $this->getGroupedData($end_date, $data_point_count);
		}

		return $data;
	}

	/**
	 * Get the data (StatValue) for the Stat
	 *
	 * @param \DateTime $end_date The end date, we work backwards from this
	 * @param int $data_point_count The number of data points to retrieve
	 */
	public function getUngroupedData(\DateTime $end_date, $data_point_count)
	{
		$data = array();

		// Get the StatValue's

		return $data;
	}

	/**
	 * Get the grouped data (StatValueGroup) for the Stat
	 *
	 * @param \DateTime $end_date The end date, we work backwards from this
	 * @param int $data_point_count The number of data points to retrieve
	 */
	public function getGroupedData(\DateTime $end_date, $data_point_count)
	{
		$data = array();

		// Get the StatValueGroup's

		return $data;
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
			$results[$k] = ucwords($grouping['table']);
		}

		return $results;
	}
}