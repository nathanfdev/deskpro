<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Reports;

use Application\DeskPRO\Entity\ReportDashboard as ReportDashboardEntity;
use Application\DeskPRO\Entity\ReportDashboardPermission;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ReportDashboard.
 */
class ReportDashboard
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isDefault;

    /**
     * @JMS\Type("collection<Application\DeskPRO\Entity\ReportDashboardPermission>")
     *
     * @var ReportDashboardPermission[]|ArrayCollection
     */
    private $permissions;

    /**
     * Constructor.
     *
     * @param ReportDashboardEntity $entity
     */
    public function __construct(ReportDashboardEntity $entity)
    {
        $this->id          = $entity->getId();
        $this->title       = $entity->getTitle();
        $this->isDefault   = $entity->isDefault();
        $this->permissions = $entity->getPermissions();
    }
}
