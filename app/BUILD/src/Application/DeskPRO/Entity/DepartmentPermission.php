<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Stores who has access to departments.
 */
class DepartmentPermission extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * Name of the "full access" permission.
     */
    const FULL = 'full';

    /**
     * Name of the "assign" permission.
     */
    const ASSIGN = 'assign';

    /**
     * name of the "tickets" app.
     */
    const APP_TICKETS = 'tickets';

    /**
     * name of the "chat" app.
     */
    const APP_CHAT = 'chat';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\Department
     */
    protected $department = null;

    /**
     * The connected usergroup. If this is set, then person cannot be set.
     *
     * @var \Application\DeskPRO\Entity\Department
     */
    protected $usergroup = null;

    /**
     * The connected person. If this is set, then usergroup cannot be set.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * @var string
     */
    protected $app;

    /**
     * The name of the permission.
     *
     * @var string
     */
    protected $name = null;

    /**
     * Any numeric number (ex filesize, flag).
     *
     * @var int
     */
    protected $value = null;

    /**
     * @see Permission::$is_active doc
     *
     * @var bool
     */
    protected $is_active = true;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setUsergroup($ug)
    {
        $this->setModelField('usergroup', $ug);

        if ($ug !== null) {
            $this->person = null;
        }
    }

    public function setPerson($p)
    {
        $this->setModelField('person', $p);

        if ($p !== null) {
            $this->usergroup = null;
        }
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function setDepartment($department)
    {
        $this->setModelField('department', $department);

        return $this;
    }

    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->setModelField('value', $value);

        return $this;
    }

    /**
     * @param string $app
     *
     * @return $this
     */
    public function setApp($app)
    {
        $this->setModelField('app', $app);

        return $this;
    }

    /**
     * A name that identifies this permission (eg could be used as an map key).
     *
     * @return string
     */
    public function getPermissionSysId()
    {
        $x = $this->department->id.'.'.$this->app.'.';
        if ($this->usergroup) {
            $x .= 'ug'.$this->usergroup->id;
        } elseif ($this->person) {
            $x .= 'p'.$this->person->id;
        }
        $x .= '.'.$this->name.'.1';

        return $x;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\DepartmentPermission';
        $metadata->setPrimaryTable([
            'name'    => 'department_permissions',
            'indexes' => [
                'is_active_idx' => ['columns' => ['is_active']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'app',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'app',
        ]);
        $metadata->mapField([
            'fieldName'  => 'name',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'name',
        ]);
        $metadata->mapField([
            'fieldName'  => 'value',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'value',
        ]);
        $metadata->mapField(
            [
                'fieldName'  => 'is_active',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'options'    => ['default' => '1'],
                'columnName' => 'is_active',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'department',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Department',
            'mappedBy'     => null,
            'inversedBy'   => 'permissions',
            'joinColumns'  => [
                [
                    'name'                 => 'department_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'usergroup',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Usergroup',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'usergroup_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => 'department_permissions',
            'joinColumns'  => [
                [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);
    }
}
