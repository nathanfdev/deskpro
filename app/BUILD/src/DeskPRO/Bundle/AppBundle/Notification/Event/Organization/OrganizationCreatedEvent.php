<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
