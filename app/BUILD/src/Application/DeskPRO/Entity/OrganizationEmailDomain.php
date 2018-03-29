<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Maps known company domains to their company objects.
 *
 * @UniqueEntity("domain")
 */
class OrganizationEmailDomain extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The email domain.
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $domain;

    /**
     * The users organization.
     *
     * @var \Application\DeskPRO\Entity\Organization
     */
    protected $organization = null;

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     *
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     *
     * @return $this
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->domain;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\OrganizationEmailDomain';
        $metadata->setPrimaryTable(['name' => 'organization_email_domains']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'domain',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'domain',
            'id'         => true,
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'organization',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Organization',
            'mappedBy'     => null,
            'inversedBy'   => 'email_domains',
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
    }
}
