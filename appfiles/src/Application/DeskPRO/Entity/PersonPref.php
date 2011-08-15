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

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Every person can have various data or preferences associated with their account.
 * These are just key value pairs basically.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\PersonPref")
 * @ORM_Mapping\Table(name="people_prefs")
 */
class PersonPref extends \Application\DeskPRO\Domain\DomainObject
{

	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", inversedBy="preferences")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;

	/**
	 * The name of the pref
	 *
	 * @var string
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="name", type="string", length=255)
	 */
	protected $name;

	/**
	 * String value
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="value_str", type="text", nullable=true)
	 */
	protected $value_str = null;

	/**
	 * Array value
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="value_array", type="array", nullable=true)
	 */
	protected $value_array = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_expire",type="datetime", nullable=true)
	 */
	protected $date_expire = null;


	
	public function getValue()
	{
		return is_array($this->value_array) ? $this->value_array : $this->value_str;
	}

	public function setValue($val)
	{
		$this->value_str = null;
		$this->value_array = null;

		if (is_array($val)) {
			$this->value_array = $val;
		} else {
			$this->value_str = (string)$val;
		}
	}
}