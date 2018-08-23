<?php

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use JMS\Serializer\Annotation as JMS;

/**
 * Class OrganizationPhoneNumber.
 */
class OrganizationPhoneNumber extends AbstractPhoneNumber
{
    /**
     * @var \Application\DeskPRO\Entity\Organization
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     */
    protected $organization;

    /**
     * @param Organization $organization
     *
     * @return $this
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->setModelField('organization', $organization);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner($owner)
    {
        return $this->setOrganization($owner);
    }

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
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'organization',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Organization',
                'mappedBy'     => null,
                'inversedBy'   => 'phone_numbers',
                'cascade'      => ['persist'],
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'organization_id',
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
