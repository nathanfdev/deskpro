<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\Organization;

use Application\DeskPRO\Entity\Organization;
use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractLegacyEvent;

/**
 * Class OrganizationCreatedEvent.
 */
class OrganizationCreatedEvent extends AbstractLegacyEvent
{
    const EVENT_NAME = 'organization.created';

    /**
     * @var int
     */
    protected $organizationId;

    /**
     * @var string
     */
    protected $organizationName;

    /**
     * @var int
     */
    protected $organizationDateCreated;

    /**
     * @param Organization $organization
     */
    public function __construct(Organization $organization)
    {
        parent::__construct('agent.org.added');
        $this->organizationId          = $organization->getId();
        $this->organizationName        = $organization->getName();
        $this->organizationDateCreated = $organization->getDateCreated()->getTimestamp();
    }

    /**
     * @return int
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     * @return int
     */
    public function getOrganizationDateCreated()
    {
        return $this->organizationDateCreated;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return array_merge(
            parent::__sleep(),
            [
                'organizationId',
                'organizationName',
                'organizationDateCreated',
            ]
        );
    }
}
