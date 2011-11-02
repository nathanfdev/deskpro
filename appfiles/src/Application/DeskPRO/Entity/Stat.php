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
	const FREQUENCY_HOURLY = "hourly";
	const FREQUENCY_DAILY  = "daily";
	const FREQUENCY_WEEKLY = "weekly";
	
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
	 * @ORM_MAPPING\OneToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="author_id", referencedColumnName="id")
	 */
	protected $author;
	
	/**
	 * Is the stat starred
	 *
	 * @var boolean
	 * @ORM_MAPPING\Column(name="starred", type="boolean")
	 */
	protected $starred;
	
	/**
	 * The stat this stat was cloned from (its parent)
	 *
	 * @var \Application\DeskPRO\Entity\Stat
	 * @ORM_MAPPING\OneToOne(targetEntity="Stat")
	 * @ORM_MAPPING\JoinColumn(name="parent_stat_id", referencedColumnName="id")
	 */
	protected $parent_stat;
	
	/**
	 * Is the stat disabled
	 *
	 * @var bool
	 * @ORM_MAPPING\Column(name="disabled", type="boolean")
	 */
	protected $disabled;
	
	/**
	 * The frequency to run the stat
	 *
	 * @var string
	 * @ORM_MAPPING\Column(name="run_frequency", type="string", length="10")
	 */
	protected $run_frequency;
	
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
		$this->disabled     = false;
		$this->date_created = new \DateTime();
	}

}