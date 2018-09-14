<?php

namespace DeskPRO\Bundle\ReportBundle\Serializer\Model;

use Application\DeskPRO\Entity\ReportDashboard as ReportDashboardEntity;
use Application\DeskPRO\Entity\ReportDashboardPermission;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\InlineCustomSideload;
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
     * @JMS\Type("raw")
     *
     * @var InlineCustomSideload
     */
    private $reports;

    /**
     * Constructor.
     *
     * @param ReportDashboardEntity $entity
     */
    public function __construct(ReportDashboardEntity $entity, $allAdmins)
    {
        $this->id         = $entity->getId();
        $this->title      = $entity->getTitle();
        $this->isDefault  = $entity->isDefault();
        $this->isAgent    = $entity->isAgent();
        $permissionsArray = [];
        foreach ($entity->getPermissions() as $permission) {
            if ($permission->getPerson()) {
                $permissionsArray[$permission->getPerson()->getId()] = $permission;
            } else {
                $permissionsArray[] = $permission;
            }
        }
        foreach ($allAdmins as $admin) {
            if (!isset($permissionsArray[$admin->getId()])) {
                $fakePermission = new ReportDashboardPermission();
                $fakePermission->setPerson($admin)->setName(ReportDashboardPermission::FULL);
                $permissionsArray[] = ($fakePermission);
            }
        }
        $this->permissions = array_filter($permissionsArray, function ($permission) {
            /** @var ReportDashboardPermission $permission */
            if ($permission->getAgent() && !$permission->getAgent()->isActiveAgent()) {
                return false;
            }

            return true;
        });
    }

    /**
     * @return InlineCustomSideload
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * @param InlineCustomSideload $reports
     */
    public function setReports(InlineCustomSideload $reports)
    {
        $this->reports = $reports;
    }
}
