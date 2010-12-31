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

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Every person can have various data or preferences associated with their account.
 * These are just key value pairs basically.
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\PersonPref")
 * @orm:Table(name="people_prefs")
 */
class PersonPref extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The person ID
	 *
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Person", inversedBy="preferences")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * The name of the pref
	 *
	 * @var string
	 * @orm:Id
	 * @orm:Column(name="name", type="string", length=255)
	 */
	protected $name;

	/**
	 * String value
	 *
	 * @var string
	 * @orm:Column(name="value_str", type="text", nullable=true)
	 */
	protected $value_str = null;

	/**
	 * Array value
	 *
	 * @var array
	 * @orm:Column(name="value_array", type="array", nullable=true)
	 */
	protected $value_array = null;


	
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