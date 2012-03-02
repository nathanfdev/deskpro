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
