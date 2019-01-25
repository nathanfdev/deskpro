<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\EntityRepository\Usergroup as UsergroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

/**
 * A usergroup is any way to group related users together. Not necessarily just for permissions.
 *
 * @property int                          $id
 * @property string                       $title
 * @property string                       $note
 * @property bool                         $is_agent_group
 * @property string                       $sys_name
 * @property bool                         $is_enabled
 * @property Permission[]|ArrayCollection $permissions
 *
 * @JMS\ExclusionPolicy("all")
 */
class Usergroup extends DomainObject
{
    const EVERYONE            = 'everyone';
    const REGISTERED          = 'registered';
    const AGENT_ALL_PERM      = 'agent_all_perms';
    const AGENT_ALL_SAFE_PERM = 'agent_all_safe_perms';

    /**
     * The unique ID, DB-generated.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * Title of the usergroup.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * A note or description about the usergroup.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $note = '';

    /**
     * Is this an agent group?
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_agent_group = false;

    /**
     * When non-null, the group is a special system group (hidden from most interfaces).
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string|null
     */
    protected $sys_name = null;

    /**
     * Is the group enabled?
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_enabled = true;

    /**
     * Usergroup permissions.
     *
     * @JMS\Expose()
     * @JMS\Type("Application\DeskPRO\Entity\Permission")
     *
     * @var ArrayCollection|Permission[]
     */
    protected $permissions;

    /**
     * @var DepartmentPermission[]
     */
    protected $department_permissions;

    /**
     * Usergroup members.
     *
     * @var ArrayCollection|Person[]
     */
    protected $people;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->permissions            = new ArrayCollection();
        $this->department_permissions = new ArrayCollection();
        $this->people                 = new ArrayCollection();
    }

    /**
     * @return Usergroup
     */
    public static function createUsergroup()
    {
        return new self();
    }

    /**
     * @param Permission $permission
     *
     * @return $this
     */
    public function addPermission(Permission $permission)
    {
        $this->permissions->add($permission);
        $this->_onPropertyChanged('permissions', $this->permissions, $this->permissions);

        return $this;
    }

    /**
     * @param Permission $permission
     *
     * @return $this
     */
    public function removePermission(Permission $permission)
    {
        $this->permissions->removeElement($permission);
        $this->_onPropertyChanged('permissions', $this->permissions, $this->permissions);

        return $this;
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
     * @return string
     */
    public function getSysName()
    {
        return $this->sys_name;
    }

    /**
     * @return bool
     */
    public function hasAllPermissions()
    {
        return $this->sys_name === self::AGENT_ALL_PERM;
    }

    /**
     * @return bool
     */
    public function hasAllSafePermissions()
    {
        return $this->sys_name === self::AGENT_ALL_SAFE_PERM || $this->sys_name === self::AGENT_ALL_PERM;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * Set note.
     *
     * @param string $note
     *
     * @return $this
     */
    public function setNote($note)
    {
        $this->setModelField('note', $note);

        return $this;
    }

    /**
     * Set system name.
     *
     * @param string $sysName
     *
     * @return $this
     */
    public function setSysName($sysName)
    {
        $this->setModelField('sys_name', $sysName);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAgentGroup()
    {
        return $this->is_agent_group;
    }

    /**
     * @param bool $is_agent_group
     *
     * @return $this
     */
    public function setIsAgentGroup($is_agent_group)
    {
        $this->setModelField('is_agent_group', $is_agent_group);

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->is_enabled;
    }

    public function getPeople()
    {
        return $this->people;
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
        $usergroup_ids = [];

        foreach ($usergroups as $ug) {
            if (is_object($ug)) {
                $usergroup_ids[] = $ug['id'];
            } else {
                $usergroup_ids[] = (int) $ug;
            }
        }

        if ($usergroup_ids) {
            $usergroup_ids = array_unique($usergroup_ids, \SORT_NUMERIC);
            sort($usergroup_ids, \SORT_NUMERIC);
        } else {
            $usergroup_ids = [0];
        }

        return md5(implode(',', $usergroup_ids));
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('title', new NotBlank());
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = UsergroupRepository::class;
        $metadata->setPrimaryTable(['name' => 'usergroups']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'title',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'title',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'note',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'note',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_agent_group',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_agent_group',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'sys_name',
                'type'       => 'string',
                'length'     => 50,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'sys_name',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_enabled',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_enabled',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'permissions',
                'targetEntity' => Permission::class,
                'mappedBy'     => 'usergroup',
                'cascade'      => [
                    'persist',
                    'remove',
                ],
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'department_permissions',
                'targetEntity' => DepartmentPermission::class,
                'mappedBy'     => 'usergroup',
                'cascade'      => [
                    'persist',
                    'remove',
                ],
                'orphanRemoval' => true,
            ]
        );

        $metadata->mapManyToMany(
            [
                'fieldName'    => 'people',
                'targetEntity' => Person::class,
                'mappedBy'     => 'usergroups',
                'fetch'        => ClassMetadataInfo::FETCH_EXTRA_LAZY,
            ]
        );

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
