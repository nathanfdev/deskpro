<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Web;

/**
 * Settings used by the system.
 *
 * @orm:Entity
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="property_type", type="string")
 * @DiscriminatorMap({"permission" = "UsergroupPropertyPermission"})
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="usergroup_properties")
 */
abstract class UsergroupProperty extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The name of the property. This may be quite important if the system uses the property.
	 *
	 * @var string
	 * @orm:Column(name="name", type="string", length=50)
	 */
	protected $name = null;


	/**
	 * The usergroup this property belongs to
	 *
	 * @var Application\CoreBundle\Entity\Usergroup
	 * @orm:OneToOne(targetEntity="Usergroup", mappedBy="properties")
	 * @orm:JoinColumn(name="usergroup_id", referencedColumnName="id")
	 */
	protected $usergroup;


	/**
	 * An flag value
	 *
	 * @var bool
	 * @orm:Column(name="flag", type="boolean", nullable=true)
	 */
	protected $flag = null;


	/**
	 * Any arbitrary value
	 *
	 * @var bool
	 * @orm:Column(name="data", type="text", nullable=true)
	 */
	protected $data = null;


	
	/**
	 * Get either the flag value, or the data value, or null.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		if ($this->flag !== null) {
			return $this->flag;
		} elseif ($this->data !== null) {
			return $this->data;
		} else {
			return null;
		}
	}



	public function __toString()
	{
		$str = '[' . $this->name . ':';
		if ($this->flag !== null) {
			$str .= $prop->flag ? 'yes' : 'no';
		} elseif ($prop->data !== null) {
			$str .= $prop->data;
		} else {
			$str .= 'NULL';
		}
		$str .= ']';

		return $str;
	}
}