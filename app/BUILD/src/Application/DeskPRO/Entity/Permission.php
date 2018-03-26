<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

/**
 * Permissions are flags applied groups or specific users.
 *
 * @property int       $id
 * @property string    $name
 * @property Usergroup $usergroup
 * @property Person    $person
 * @property bool      $value
 * @property bool      $is_active
 *
 * @JMS\ExclusionPolicy("all")
 */
class Permission extends DomainObject
{
    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * The name of the permission.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $name = null;

    /**
     * The usergroup this properly belongs to. Note that a permission applies to either
     * a person or a usergroup, never both.
     *
     * @var Usergroup
     */
    protected $usergroup;

    /**
     * The person this properly belongs to. Note that a permission applies to either
     * a person or a usergroup, never both.
     *
     * @var Person
     */
    protected $person;

    /**
     * Any numeric number (ex filesize, flag).
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $value = null;

    /**
     * True means this permission record is active for normal use with the permission resolver.
     *
     * When a $person permission is used but the $person in question is also part
     * of a usergroup, then this record might be superfluous: If the ug grants the perm,
     * and this record grants the perm, then we have two records that both grant the perm.
     *
     * This isn't harmful usually but if you have many many agents defined and they all have
     * these duplicative perms, then you end up with many thousands of extra rows, which are all
     * fetched and processed with the permission resolver.
     *
     * So we turn these extra perms "off" so the resolver doesn't fetch them. That means if a
     * hd with many agent uses groups instead of overrides, permission resolving is much much faster.
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
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

    public function __toString()
    {
        $str = '['.$this->name.':';

        if ($prop->value !== null) {
            $str .= $prop->data;
        } else {
            $str .= 'NULL';
        }

        $str .= ']';

        return $str;
    }

    /**
     * Combine an array of permissions into a superduper array of effective permissions.
     *
     * @param self[]|array $perms
     * @param array        $mergeEffectivePermissions
     *
     * @return array
     */
    public static function getEffectivePermissions(array $perms, array $mergeEffectivePermissions = [])
    {
        $effective_perms = $mergeEffectivePermissions;

        foreach ($perms as $perm) {
            if (is_array($perm)) {
                $k = $perm['name'];
                $v = $perm['value'];
            } else {
                /** @var self $k */
                $k = $perm->getName();
                $v = $perm->getValue();
            }

            if (is_scalar($v)) {
                $v = (int) $v;
            }

            // If it hasnt been set yet, or the one we have is "lower",
            // then take the new value.
            if (!isset($effective_perms[$k]) || (is_int($v) && $effective_perms[$k] < $v)) {
                $effective_perms[$k] = $v;
            }
        }

        return $effective_perms;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->setModelField('value', $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $name
     *
     * @return Permission
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
     * @param Person $person
     *
     * @return Permission
     */
    public function setPerson(Person $person)
    {
        $this->setModelField('person', $person);

        return $this;
    }
    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable([
            'name'    => 'permissions',
            'indexes' => [
                'is_active_idx' => ['columns' => ['is_active']],
            ],
        ]);
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
                'fieldName'  => 'name',
                'type'       => 'string',
                'length'     => 50,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'name',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'value',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'value',
            ]
        );
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
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'usergroup',
                'targetEntity' => Usergroup::class,
                'inversedBy'   => 'permissions',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'usergroup_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => Person::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
    }
}
