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
 * Stat Value Group - The grouping data for a Stat Value
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\StatValueGroup")
 * @ORM_Mapping\Table(name="stat_value_group")
 */
class StatValueGroup extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The Stat Value
	 *
	 * @var \Application\DeskPRO\Entity\StatValue
	 * @ORM_MAPPING\ManyToOne(targetEntity="StatValue", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="stat_value_id", referencedColumnName="id")
	 */
	protected $stat_value;

	/**
	 * The Grouping Id
	 *
	 * @var int
	 * @ORM_MAPPING\Column(name="grouping_id", type="integer", nullable=true)
	 */
	protected $grouping_id;

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
}