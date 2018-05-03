<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @property int $id
 * @property bool $is_global
 * @property AppInstance $app_instance
 * @property Usergroup $usergroup
 * @property Person $person
 */
class AppInstancePermission extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var AppInstance
     */
    protected $app_instance;

    /**
     * @var Usergroup
     */
    protected $usergroup;

    /**
     * @var Person
     */
    protected $person;

    /**
     * @param Usergroup $v
     */
    public function setUsergroup(Usergroup $v)
    {
        $this->setModelField('usergroup', $v);
        $this->setModelField('person', null);
    }

    /**
     * @param Person $v
     */
    public function setPerson(Person $v)
    {
        $this->setModelField('usergroup', null);
        $this->setModelField('person', $v);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType      = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType        = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->setPrimaryTable([
            'name' => 'app_instance_permissions',
        ]);

        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'app_instance',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AppInstance',
            'fetch'        => ClassMetadataInfo::FETCH_LAZY,
            'joinColumns'  => [
                [
                    'name'                 => 'app_instance_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'CASCADE',
                ],
            ],
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'usergroup',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Usergroup',
            'joinColumns'  => [
                [
                    'name'                 => 'usergroup_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'CASCADE',
                ],
            ],
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'joinColumns'  => [
                [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'CASCADE',
                ],
            ],
        ]);
    }
}
