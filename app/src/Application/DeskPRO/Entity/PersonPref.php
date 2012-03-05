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

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Application\DeskPRO\App;

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

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value_str
     *
     * @param text $valueStr
     */
    public function setValueStr($valueStr)
    {
        $this->value_str = $valueStr;
    }

    /**
     * Get value_str
     *
     * @return text
     */
    public function getValueStr()
    {
        return $this->value_str;
    }

    /**
     * Set value_array
     *
     * @param array $valueArray
     */
    public function setValueArray($valueArray)
    {
        $this->value_array = $valueArray;
    }

    /**
     * Get value_array
     *
     * @return array
     */
    public function getValueArray()
    {
        return $this->value_array;
    }

    /**
     * Set date_expire
     *
     * @param datetime $dateExpire
     */
    public function setDateExpire($dateExpire)
    {
        $this->date_expire = $dateExpire;
    }

    /**
     * Get date_expire
     *
     * @return datetime
     */
    public function getDateExpire()
    {
        return $this->date_expire;
    }

    /**
     * Set person
     *
     * @param Application\DeskPRO\Entity\Person $person
     */
    public function setPerson(\Application\DeskPRO\Entity\Person $person)
    {
        $this->person = $person;
    }

    /**
     * Get person
     *
     * @return Application\DeskPRO\Entity\Person
     */
    public function getPerson()
    {
        return $this->person;
    }



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\PersonPref'; 
		$metadata->setPrimaryTable(array( 'name' => 'people_prefs', )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT); 
		$metadata->mapField(array( 'fieldName' => 'name', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'name', 'id' => true, )); 
		$metadata->mapField(array( 'fieldName' => 'value_str', 'type' => 'text', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'value_str', )); 
		$metadata->mapField(array( 'fieldName' => 'value_array', 'type' => 'array', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'value_array', )); 
		$metadata->mapField(array( 'fieldName' => 'date_expire', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'date_expire', )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => 'preferences', 'joinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, ));
	}
}

