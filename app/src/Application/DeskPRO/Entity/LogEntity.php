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
//use Application\DeskPRO\Log\Loggable;
use Application\DeskPRO\ORM\StateChange\ChangeInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class LogEntity extends DomainObject// implements Loggable
{
	protected $id;

	protected $timestamp;

	/** @var Person context person */
	protected $person;

	protected $entity;

	protected $property;

	protected $old;

	protected $new;

	protected $message;

	/** @var \Application\DeskPRO\ORM\StateChange\ChangeInterface  */
	protected $change;

	public function __construct(DomainObject $entity, ChangeInterface $change, Person $person = null)
	{
		$this['timestamp'] = time();
		$this['entity'] = get_class($entity);
		$this->person = $person;
		$this->change = $change;
	}

	/**
	 * todo
	 * @return string
	 */
	public function __toString()
	{
		return sprintf('Round Robin entry');
	}

	public function context()
	{
		return array();
	}

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->setPrimaryTable(array(
			'name' => 'log_entity',
			'indexes' => array(
				'entity' => array('columns' => array('entity', 'property')),
			)
		));
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'nullable' => false, 'id' => true, 'options' => array('unsigned' => true)));
		$metadata->mapField(array( 'fieldName' => 'timestamp', 'type' => 'integer', 'nullable' => false, 'options' => array('unsigned' => true)));
		$metadata->mapField(array( 'fieldName' => 'entity', 'type' => 'string', 'nullable' => false));
		$metadata->mapField(array( 'fieldName' => 'property', 'type' => 'string', 'nullable' => false));
		$metadata->mapField(array( 'fieldName' => 'old', 'type' => 'integer', 'nullable' => false));
		$metadata->mapField(array( 'fieldName' => 'new', 'type' => 'integer', 'nullable' => false));
		$metadata->mapField(array( 'fieldName' => 'message', 'type' => 'text', 'nullable' => false));

		$metadata->mapOneToOne(array(
			'fieldName'            => 'person',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\Person',
			'joinColumns'          => array(array(
				'name'                 => 'person_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			)),
			'dpApi'                => true,
		));

		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}