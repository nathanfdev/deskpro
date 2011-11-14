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
 * Stat Value - A row represent a unit of data for a Stat
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\StatValue")
 * @ORM_Mapping\Table(name="stat_value")
 */
class StatValue extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The Stat
	 *
	 * @var \Application\DeskPRO\Entity\Stat
	 * @ORM_MAPPING\ManyToOne(targetEntity="Stat", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="stat_id", referencedColumnName="id")
	 */
	protected $stat;

	/**
	 * The stat value
	 *
	 * @var int
	 * @ORM_MAPPING\Column(name="value", type="decimal")
	 */
	protected $value;

	/**
	 * The unix time for the period this stat represents
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="stat_unix", type="integer")
	 */
	protected $stat_unix;

	public function __construct()
	{
		$this->stat_unix = time();
	}

	/**
	 * Get a StatValueGroup by date
	 *
	 * @param \DateTime $date The DateTime to check
	 * @return StatValueGroup The found StatValueGroup entity or null
	 */
	public function getStatValueGroupForDate(\DateTime $date, $grouping_id, $run_frequency)
	{
		$stat_value_group = null;

		$repo = App::getEntityRepository('DeskPRO:StatValueGroup');

		switch ($run_frequency) {
			case 'daily':
				$stat_value_group = $repo->getForStatValueByDay($this->id, $grouping_id, $date);
				break;
			case 'monthly':
				$stat_value_group = $repo->getForStatValuepByMonth($this->id, $grouping_id, $date);
				break;
			case 'yearly':
				$stat_value_group = $repo->getForStatValueByYear($this->id, $grouping_id, $date);
				break;
			default:
				throw new \Exception("Unable to retrieve StatValueGroup for run_frequency " . $run_frequency);
		}

		return $stat_value_group;
	}
}