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
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Web;

/**
 * Settings used by the system.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\InheritanceType("SINGLE_TABLE")
 * @ORM_Mapping\DiscriminatorColumn(name="property_type", type="string")
 * @ORM_Mapping\DiscriminatorMap({"permission" = "UsergroupPropertyPermission"})
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="usergroup_properties")
 */
abstract class UsergroupProperty extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 * 
	 */
	protected $id = null;

	/**
	 * The name of the property. This may be quite important if the system uses the property.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="string", length=50)
	 */
	protected $name = null;


	/**
	 * The usergroup this property belongs to
	 *
	 * @var Application\DeskPRO\Entity\Usergroup
	 * @ORM_Mapping\OneToOne(targetEntity="Usergroup", mappedBy="properties")
	 * @ORM_Mapping\JoinColumn(name="usergroup_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $usergroup;


	/**
	 * An flag value
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="flag", type="boolean", nullable=true)
	 */
	protected $flag = null;


	/**
	 * Any arbitrary value
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="data", type="text", nullable=true)
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