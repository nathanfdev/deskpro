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
 * Custom ticket data
 */
class CustomFieldData extends CustomDataAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\CustomFieldDefinition
	 */
	protected $definition;

	/**
	 * @var int
	 */
	protected $owner_id;


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setPrimaryTable(array('name' => 'custom_field_data'));
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\CustomFieldData';
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);

		$metadata->mapField(array(
			'fieldName' => 'id',
			'type' => 'integer',
			'nullable' => false,
			'columnName' => 'id',
			'id' => true,
		));

		$metadata->mapManyToOne(array(
			'fieldName' => 'definition',
			'targetEntity' => 'Application\DeskPRO\Entity\CustomFieldDefinition',
			'joinColumns' => array(
				array(
					'name' => 'definition_id',
					'referencedColumnName' => 'id',
					'onDelete' => 'cascade',
				),
			),
		));

		$metadata->mapField(array(
			'fieldName' => 'owner_id',
			'type' => 'integer',
			'nullable' => false,
			'columnName' => 'owner_id',
		));

		$metadata->mapField(array(
			'fieldName' => 'value',
			'type' => 'integer',
			'nullable' => false,
			'columnName' => 'value',
		));

		$metadata->mapField(array(
			'fieldName' => 'input',
			'type' => 'text',
			'nullable' => false,
			'columnName' => 'input',
		));
	}
}
