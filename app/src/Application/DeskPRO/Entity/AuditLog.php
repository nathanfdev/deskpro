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
use Orb\Util\Strings;

/**
 * AuditLog
 */
class AuditLog extends DomainObject
{
	const CREATE = 'create';
	const DELETE = 'delete';
	const UPDATE = 'update';
	const UPDATE_SET = 'set';

	/**
	 * @var int
	 *
	 */
	protected $id;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person = null;

	/**
	 * @var string
	 */
	protected $person_name = null;

	/**
	 * @var string
	 */
	protected $op;

	/**
	 * @var string
	 */
	protected $object_type;

	/**
	 * @var string
	 */
	protected $object_id;

	/**
	 * @var array
	 */
	protected $data = null;

	/**
	 * @var \DateTime
	 */
	protected $date_created;


	/**
	 * @param mixed|null $object
	 */
	public function __construct($op, $object = null)
	{
		$this->date_created = new \DateTime();
		$this->op = $op;

		if ($object) {
			$this->setObject($object);
		}
	}


	/**
	 * @param Person $person
	 */
	public function setPerson(Person $person)
	{
		$this->setModelField('person', $person);
		$this->setModelField('person_name', $person->getDisplayContact());
	}


	/**
	 * @param string $context Optinally context id
	 */
	public function setSystemPerson($context = null)
	{
		$this['person'] = null;
		$this['person_name'] = 'System';

		if ($context) {
			$this['person_name'] .= " <$context>";
		}
	}


	/**
	 * @param mixed $object
	 * @throws \InvalidArgumentException When type/id could not be detected from object
	 */
	public function setObject($object)
	{
		$object_type = self::getObjectTypeFromVar($object);
		$object_id   = self::getObjectIdFromVar($object);

		if (!$object_type || !$object_id) {
			throw new \InvalidArgumentException("Could not determine object_type and object_id from object");
		}

		$this['object_type'] = $object_type;
		$this['object_id']   = $object_id;
	}


	/**
	 * Add data that was cahgned.
	 *
	 * @param string $field_id The field that was changed
	 * @param string $old_val  The old field value
	 * @param string $new_val  The new field value
	 */
	public function addChangeData($field_id, $old_val, $new_val)
	{
		if (!$this->data) $this->data = array();

		$this->data[] = array(
			'type'     => self::UPDATE,
			'field_id' => $field_id,
			'old_val'  => $old_val,
			'new_val'  => $new_val
		);
	}


	/**
	 * Ge the object type/id in standard naming format
	 *
	 * @return string
	 */
	public function getObjectName()
	{
		return $this->object_type . '@' . $this->object_id;
	}


	/**
	 * Get an object type from an object param:
	 * - A DomainObject with getTableName()
	 * - A string in form of type@id
	 *
	 * @param mixed $object
	 * @return string
	 */
	public static function getObjectTypeFromVar($object)
	{
		if (is_object($object) && $object instanceof DomainObject) {
			return $object->getTableName();
		} else if (is_array($object) && isset($object['type']) && isset($object['id'])) {
			return $object['type'];
		} else {
			if (is_object($object)) {
				return get_class($object);
			} else if (is_string($object) || ctype_digit($object)) {
				if ($object_type = Strings::extractRegexMatch('#^(.*?)@(\d+)$#', $object)) {
					return $object_type;
				}
				return (string)$object;
			} else {
				return null;
			}
		}
	}


	/**
	 * Get an object ID from an object param:
	 * - A DomainObject with getId
	 * - A string in form of type@id
	 *
	 * @param mixed $object
	 * @return string|null
	 */
	public static function getObjectIdFromVar($object)
	{
		if (is_object($object) && $object instanceof DomainObject) {
			if ($object instanceof Setting) {
				return $object->name;
			} else {
				return $object->getId();
			}
		} else if (is_array($object) && isset($object['type']) && isset($object['id'])) {
			return $object['id'];
		} else if (is_string($object) && $id = Strings::extractRegexMatch('#@(\d+)$#', $object)) {
			return $id;
		}

		return null;
	}


	/**
	 * Get standard object name for an object
	 *
	 * @param mixed $object
	 * @return null|string
	 */
	public static function getObjectNameFromVar($object)
	{
		$object_type = self::getObjectTypeFromVar($object);
		$object_id   = self::getObjectIdFromVar($object);

		if (!$object_type || !$object_id) {
			return null;
		}

		return "$object_type@$object_id";
	}

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		//$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\AuditLog';
		$metadata->setPrimaryTable(array(
			'name' => 'auditlog',
		));

		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'person_name', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'person_name', ));
		$metadata->mapField(array( 'fieldName' => 'op', 'type' => 'string', 'length' => 20, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'op', ));
		$metadata->mapField(array( 'fieldName' => 'object_type', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'object_type', ));
		$metadata->mapField(array( 'fieldName' => 'object_id', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'object_id', ));
		$metadata->mapField(array( 'fieldName' => 'data', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'data', ));
		$metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'date_created', ));
		$metadata->mapManyToOne(array( 'fieldName' => 'person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ) ));
	}
}
