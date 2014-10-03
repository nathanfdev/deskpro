<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

/**
 * A usergroup is any way to group related users together. Not necessarily just for permissions.
 *
 * @property int $id
 * @property string $title
 * @property string $note
 * @property boolean $is_agent_group
 * @property string $sys_name
 * @property boolean $is_enabled
 * @property Permission[] $permissions
 * @SWG\Model(id="Usergroup")
 */
class Usergroup extends DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @SWG\Property(name="id", type="integer")
	 */

	protected $id = null;

	/**
	 * Title of the usergroup
	 *
	 * @var string
	 * @SWG\Property(name="title", type="string")
	 */

	protected $title;

	/**
	 * A note or description about the usergroup
	 *
	 * @var string
	 * @SWG\Property(name="note", type="string")
	 */

	protected $note = '';

	/**
	 * Is this an agent group?
	 *
	 * @var bool
	 * @SWG\Property(name="is_agent_group", type="boolean")
	 */

	protected $is_agent_group = false;

	/**
	 * When non-null, the group is a special system group (hidden from most interfaces).
	 *
	 * @var bool
	 * @SWG\Property(name="sys_name", type="string")
	 */

	protected $sys_name = null;

	/**
	 * Is the group enabled?
	 *
	 * @var bool
	 * @SWG\Property(name="is_enabled", type="boolean")
	 */

	protected $is_enabled = true;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @SWG\Property(name="permissions", type="array", @SWG\Items("Permission"))
	 */

	protected $permissions;

	/**
	 * Constructor
	 */

	public function __construct()
	{
		$this->permissions = new ArrayCollection();
	}

	/**
	 * @return Usergroup
	 */

	public static function createUsergroup()
	{
		return new self();
	}

	/**
	 * @param $permission
	 */

	public function addPermission($permission)
	{
		$this->permissions->add($permission);
	}

	/**
	 * @param $permission
	 */

	public function removePermission($permission)
	{
		$this->permissions->removeElement($permission);
	}

	/**
	 * @return int
	 */

	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */

	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Generate a key for a set of usergroups. These same usergroups
	 * will always generate the same key.
	 *
	 * @static
	 *
	 * @param array $usergroups Array of usergroup IDs or usergroup objects
	 *
	 * @return string
	 */

	public static function generateUsergroupSetKey(array $usergroups)
	{
		$usergroup_ids = array();

		foreach ($usergroups as $ug) {

			if (is_object($ug)) {

				$usergroup_ids[] = $ug['id'];

			} else {

				$usergroup_ids[] = (int)$ug;
			}
		}

		if ($usergroup_ids) {

			$usergroup_ids = array_unique($usergroup_ids, \SORT_NUMERIC);
			sort($usergroup_ids, \SORT_NUMERIC);

		} else {

			$usergroup_ids = array(0);
		}

		return md5(implode(',', $usergroup_ids));
	}


	############################################################################
	# Validation Metadata
	############################################################################

	public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('title', new NotBlank());
	}


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Usergroup';
		$metadata->setPrimaryTable(array('name' => 'usergroups',));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(
			array(
				 'fieldName'  => 'id',
				 'type'       => 'integer',
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'id',
				 'id'         => true,
			)
		);
		$metadata->mapField(
			array(
				 'fieldName'  => 'title',
				 'type'       => 'string',
				 'length'     => 255,
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'title',
			)
		);
		$metadata->mapField(
			array(
				 'fieldName'  => 'note',
				 'type'       => 'text',
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'note',
			)
		);
		$metadata->mapField(
			array(
				 'fieldName'  => 'is_agent_group',
				 'type'       => 'boolean',
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'is_agent_group',
			)
		);
		$metadata->mapField(
			array(
				 'fieldName'  => 'sys_name',
				 'type'       => 'string',
				 'length'     => 50,
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => true,
				 'columnName' => 'sys_name',
			)
		);
		$metadata->mapField(
			array(
				 'fieldName'  => 'is_enabled',
				 'type'       => 'boolean',
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'is_enabled',
			)
		);
		$metadata->mapOneToMany(
			array(
				 'fieldName'    => 'permissions',
				 'targetEntity' => 'Application\\DeskPRO\\Entity\\Permission',
				 'mappedBy'     => 'usergroup',
			)
		);
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}
