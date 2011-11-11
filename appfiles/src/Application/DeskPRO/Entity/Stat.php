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

	protected static $availableRunFrequencies = array(
		self::FREQUENCY_DAILY, self::FREQUENCY_MONTHLY, self::FREQUENCY_YEARLY
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

	public function setRunFrequency($frequency)
	{
		if (false === in_array($frequency, $this->availableRunFrequencies)) {
			throw new \Exception("Unable to set run frequency to type $frequency. Supported types are " . join(", ", $this->availableRunFrequencies));
		}

		$this->run_frequency = $frequency;
	}
}