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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Settings used by the system.
 *
 */
class Setting extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * 
	 */
	protected $id = null;


	/**
	 * The name of the setting
	 *
	 * @var string
	 */
	protected $name = null;


	/**
	 * Settings can belong to groups. The group is the string
	 * before the first dot in the name. deskpro.url, the group is 'deskpro'
	 *
	 * @var string
	 */
	protected $groupname;


	/**
	 * The value of a setting
	 *
	 * @var string
	 */
	protected $value;


	/**
	 * The default value set by DeskPRO.
	 *
	 * @var string
	 */
	protected $default_value = '';


	/**
	 * @var \DateTime
	 */
	protected $created_at;


	/**
	 * @var \DateTime
	 */
	protected $updated_at;



	/**
	 * Get the value of a setting.
	 *
	 * @return string
	 */
	public function getValue()
	{
		if ($this->value === null) {
			return $this->default_value;
		}

		return $this->value;
	}


	/**
	 */
	public function _resetValueIfDefault()
	{
		if ($this->value == $this->default_value) {
			$this->value = null;
		}
	}

	/**
	 */
	public function _resetGroupFromName()
	{
		$dotpos = strpos($this->name, '.');
		if ($dotpos) {
			$this->groupname = substr($this->name, 0, $dotpos);
		} else {
			$this->groupname = null;
		}
	}

	public function _incCreatedAt()
	{
		$this->created_at = $this->updated_at = new \DateTime();
	}

	public function _incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Setting'; 
		$metadata->setPrimaryTable(array( 'name' => 'settings', )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT); 
		$metadata->addLifecycleCallback('_resetValueIfDefault', 'prePersist'); 
		$metadata->addLifecycleCallback('_resetGroupFromName', 'prePersist'); 
		$metadata->addLifecycleCallback('_incCreatedAt', 'prePersist'); 
		$metadata->addLifecycleCallback('_resetValueIfDefault', 'preUpdate'); 
		$metadata->addLifecycleCallback('_resetGroupFromName', 'preUpdate'); 
		$metadata->addLifecycleCallback('_incUpdatedAt', 'preUpdate'); 
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'id', 'id' => true, )); 
		$metadata->mapField(array( 'fieldName' => 'name', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'name', )); 
		$metadata->mapField(array( 'fieldName' => 'groupname', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'groupname', )); 
		$metadata->mapField(array( 'fieldName' => 'value', 'type' => 'text', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'value', )); 
		$metadata->mapField(array( 'fieldName' => 'default_value', 'type' => 'text', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'default_value', )); 
		$metadata->mapField(array( 'fieldName' => 'created_at', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'created_at', )); 
		$metadata->mapField(array( 'fieldName' => 'updated_at', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'updated_at', )); 
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}
