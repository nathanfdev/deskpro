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

use Orb\Util\Util;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Basic ratings
 *
 * @ORM_Mapping\MappedSuperclass
 */
class RatingAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="ip_address", type="string", length=50)
	 */
	protected $ip_address;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="rating", type="integer")
	 */
	protected $rating;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	public function setRating($rating)
	{
		if ($rating > 0) {
			$this->rating = 1;
		} else {
			$this->rating = -1;
		}
	}

	public function rateUp()
	{
		$this->setRating(1);
	}

	public function rateDown()
	{
		return $this->setRating(-1);
	}
}