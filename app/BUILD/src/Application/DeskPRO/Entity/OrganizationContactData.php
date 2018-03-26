<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use DeskPRO\Bundle\AppBundle\EventListener\Doctrine\TwitterListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Contact data for an organization.
 *
 * @Assert\GroupSequenceProvider
 * @JMS\ExclusionPolicy("all")
 */
class OrganizationContactData extends ContactDataAbstract
{
    /**
     * @Assert\NotNull()
     *
     * @var \Application\DeskPRO\Entity\Organization
     */
    protected $organization;

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * {@inheritdoc}
     */
    public function getRef()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     */
    public function setOrganization($organization)
    {
        $this->setModelField('organization', $organization);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setPrimaryTable(['name' => 'organizations_contact_data']);
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
            'fieldName'  => 'contact_type',
            'type'       => 'string',
            'length'     => 80,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'contact_type',
        ]);
        $metadata->mapField([
            'fieldName'  => 'comment',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'comment',
        ]);
        $metadata->mapField([
            'fieldName'  => 'field_1',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'field_1',
        ]);
        $metadata->mapField([
            'fieldName'  => 'field_2',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'field_2',
        ]);
        $metadata->mapField([
            'fieldName'  => 'field_3',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'field_3',
        ]);
        $metadata->mapField([
            'fieldName'  => 'field_4',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'field_4',
        ]);
        $metadata->mapField([
            'fieldName'  => 'field_5',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'field_5',
        ]);
        $metadata->mapField([
            'fieldName'  => 'field_6',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'field_6',
        ]);
        $metadata->mapField([
            'fieldName'  => 'field_7',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'field_7',
        ]);
        $metadata->mapField([
            'fieldName'  => 'field_8',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'field_8',
        ]);
        $metadata->mapField([
            'fieldName'  => 'field_9',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'field_9',
        ]);
        $metadata->mapField([
            'fieldName'  => 'field_10',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'field_10',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'organization',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Organization',
            'mappedBy'     => null,
            'inversedBy'   => 'contact_data',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'organization_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);

        $metadata->addEntityListener(Events::postPersist, TwitterListener::class, Events::postPersist);
        $metadata->addEntityListener(Events::preUpdate, TwitterListener::class, Events::preUpdate);
        $metadata->addEntityListener(Events::preRemove, TwitterListener::class, Events::preRemove);
    }
}
