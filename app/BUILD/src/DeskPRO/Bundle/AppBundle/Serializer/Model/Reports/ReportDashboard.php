<?php

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
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isAgent;

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
        $this->isAgent     = $entity->isAgent();
        $this->permissions = $entity->getPermissions();
    }
}
