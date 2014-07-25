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
use Application\DeskPRO\Log\Loggable;
use Application\DeskPRO\ORM\StateChange\ChangeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class LogEntity extends DomainObject implements Loggable
{
	protected $id;

	protected $timestamp;

	protected $parent;

	protected $children;

	/** @var Person context person */
	protected $person;

	protected $entity;

	protected $property;

	protected $old;

	protected $new;

	/** @var \Application\DeskPRO\ORM\StateChange\ChangeInterface  */
	protected $change;
	/** @var DomainObject */
	protected $subject;

	public function __construct(DomainObject $subject, Person $person = null, ChangeInterface $change = null)
	{
		$this['timestamp'] = time();
		$parts = explode('\\', get_class($subject));
		$this['entity'] = end($parts);
		$this->person = $person;
		$this->subject = $subject;
		$this->children = new ArrayCollection();

		// $change is null when persisting new $subject
		if ($change) {
			$this->change = $change;
			$this['property'] = $change->getField();
		}
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
		$metadata->mapField(array( 'fieldName' => 'property', 'type' => 'string', 'nullable' => true));
		$metadata->mapField(array( 'fieldName' => 'old', 'type' => 'string', 'nullable' => true));
		$metadata->mapField(array( 'fieldName' => 'new', 'type' => 'string', 'nullable' => true));

		$metadata->mapManyToOne(array(
			'fieldName' => 'parent',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\LogEntity',
			'joinColumns' => array(0 => array(
				'nullable' => true,
				'onDelete' => 'cascade',
			),),
		));

		$metadata->mapOneToMany(array(
			'fieldName' => 'children',
			'mappedBy'  => 'parent',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\LogEntity',
		));

		$metadata->mapManyToOne(array(
			'fieldName'            => 'person',
			'targetEntity'         => 'Application\\DeskPRO\\Entity\\Person',
			'joinColumns' => array(0 => array(
				'nullable' => true,
				'onDelete' => 'cascade',
			),),
		));

		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}