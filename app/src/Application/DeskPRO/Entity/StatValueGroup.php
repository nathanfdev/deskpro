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
	 * The Grouping Reference
	 *
	 * @var string
	 * @ORM_MAPPING\Column(name="grouping_ref", type="string", length=255, nullable=true)
	 */
	protected $grouping_ref;

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



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\StatValueGroup'; 
		$metadata->setPrimaryTable(array( 'name' => 'stat_value_group', )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT); 
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'id', 'id' => true, )); 
		$metadata->mapField(array( 'fieldName' => 'grouping_ref', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'grouping_ref', )); 
		$metadata->mapField(array( 'fieldName' => 'value', 'type' => 'decimal', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'value', )); 
		$metadata->mapField(array( 'fieldName' => 'stat_unix', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'stat_unix', )); 
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY); 
		$metadata->mapOneToOne(array( 'fieldName' => 'stat_value', 'targetEntity' => 'Application\\DeskPRO\\Entity\\StatValue', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'stat_value_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => NULL, 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, ));
	}
}

