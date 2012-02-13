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

use Orb\Util\Util;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Basic ratings
 *
 * @ORM_Mapping\MappedSuperclass
 */
abstract class RatingAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\SearchLog
	 * @ORM_Mapping\ManyToOne(targetEntity="SearchLog")
	 * @ORM_Mapping\JoinColumn(name="searchlog_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $searchlog = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\Visitor
	 * @ORM_Mapping\ManyToOne(targetEntity="Visitor", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="visitor_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $visitor = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="ip_address", type="string", length=30)
	 */
	protected $ip_address = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="email", type="string", length=255, nullable=true)
	 */
	protected $email = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="string", length=255, nullable=true)
	 */
	protected $name = null;

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

	public static function create($user_rating, $use_request = true)
	{
		$rating = new static();
		$rating->rating = $user_rating;

		if ($use_request && App::has('request')) {
			if (!App::getCurrentPerson()->isGuest()) {
				$rating->person = App::getCurrentPerson();
			}

			$rating->visitor = App::getSession()->getVisitor();
		}

		return $rating;
	}

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	public function setRating($rating)
	{
		if ($rating > 0) {
			$this->setModelField('rating', 1);
		} else {
			$this->setModelField('rating', -1);
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

	public function setVisitor(Visitor $visitor = null)
	{
		$this->_onPropertyChanged('visitor', $this->visitor, $visitor);
		$this->visitor = $visitor;

		if ($visitor === null) return;

		$this['ip_address'] = $visitor['ip_address'];

		if (!$this->name AND $visitor['name']) {
			$this['name'] = $visitor['name'];
		}
		if (!$this->email AND $visitor['email']) {
			$this['email'] = $visitor['email'];
		}
	}

	public function getPersonId()
	{
		if ($this->person) {
			return $this->person->getId();
		}

		return 0;
	}

	public function getVisitorId()
	{
		if ($this->visitor) {
			return $this->visitor->getId();
		}

		return 0;
	}

	abstract public function setContentObject($obj);
}
