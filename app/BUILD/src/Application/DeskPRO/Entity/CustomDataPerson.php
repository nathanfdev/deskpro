<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\EntityRepository\CustomDataPerson as CustomDataPersonEntityRepository;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Custom person data.
 */
class CustomDataPerson extends CustomDataAbstract
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefPerson
     */
    protected $field;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefPerson
     */
    protected $root_field;

    /**
     * Set related person entity.
     *
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return int
     */
    public function getPersonId()
    {
        return $this->person['id'];
    }

    /**
     * Set a field.
     *
     * @param CustomDefPerson $field
     *
     * @return $this
     */
    public function setField(CustomDefPerson $field = null)
    {
        $this->setModelField('field', $field);

        return $this;
    }

    /**
     * Set a root field.
     *
     * @param CustomDefPerson $field
     *
     * @return $this
     */
    public function setRootField(CustomDefPerson $field = null)
    {
        $this->setModelField('root_field', $field);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return Person
     */
    public function getOwner()
    {
        return $this->person;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->customRepositoryClassName = CustomDataPersonEntityRepository::class;
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name'              => 'custom_data_person',
                'uniqueConstraints' => [],
            ]
        );
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
                'fieldName'  => 'value',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'value',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'input',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'input',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => Person::class,
                'inversedBy'   => 'custom_data',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'field',
                'targetEntity' => CustomDefPerson::class,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'field_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'root_field',
                'targetEntity' => CustomDefPerson::class,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'root_field_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );

        $events = [
            Events::prePersist,
            Events::postPersist,
            Events::preUpdate,
            Events::postUpdate,
        ];
        foreach ($events as $event) {
            $metadata->addEntityListener(
                $event,
                'Application\DeskPRO\Entity\EventListener\PersonCustomDataChangeLogListener',
                'on'.ucfirst($event)
            );
        }
    }
}
