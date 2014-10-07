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

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Custom ticket data
 */
class CustomFieldData extends DomainObject
{
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var \Application\DeskPRO\Entity\CustomFieldDefinition
	 */
	protected $definition;

	/**
	 * @var \Application\DeskPRO\Entity\CustomFieldDefinition
	 */
	protected $root_definition;

	/**
	 * @var int
	 */
	protected $owner_id;

	/**
	 * @var int
	 */
	protected $value;

	/**
	 * @var input
	 */
	protected $input;

	/**
	 * @var DomainObject
	 */
	protected $owner;

	public function __construct()
	{
		$this->value = 0;
		$this->input = '';
	}

	public function getData()
	{
		return $this->value ?: $this->input;
	}

	public function preFlush()
	{
		if ($this->owner && $this->owner['id']) {
			$this['owner_id'] = $this->owner['id'];
		}
	}

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setPrimaryTable(array(
			'name' => 'custom_field_data',
			'uniqueConstraints' => array(
				'unique_idx' => array('columns' => array('owner_id', 'definition_id'))
			),
		));
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\CustomFieldData';
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->addLifecycleCallback('preFlush', 'preFlush');

		$metadata->mapField(array(
			'fieldName' => 'id',
			'type' => 'integer',
			'nullable' => false,
			'columnName' => 'id',
			'id' => true,
		));

		$metadata->mapField(array(
			'fieldName' => 'owner_id',
			'type' => 'integer',
			'nullable' => false,
			'columnName' => 'owner_id',
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

		$metadata->mapManyToOne(array(
			'fieldName' => 'root_definition',
			'targetEntity' => 'Application\DeskPRO\Entity\CustomFieldDefinition',
			'joinColumns' => array(
				array(
					'name' => 'root_definition_id',
					'referencedColumnName' => 'id',
					'onDelete' => 'cascade',
				),
			),
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
