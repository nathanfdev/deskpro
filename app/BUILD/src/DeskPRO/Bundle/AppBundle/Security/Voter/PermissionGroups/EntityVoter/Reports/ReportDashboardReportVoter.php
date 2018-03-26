<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\Reports;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboardReport;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class ReportDashboardReportVoter.
 */
class ReportDashboardReportVoter extends AbstractReportDashboardVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return ReportDashboardReport::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        /** @var ReportDashboardReport $report */
        $report    = $context->getParent();
        $dashboard = $report ? $report->getDashboard() : null;

        switch ($attribute) {
            case PermissionGroupVoter::VIEW:
                return $dashboard && $this->canViewDashboard($dashboard, $user);
            case PermissionGroupVoter::MODIFY:
            case PermissionGroupVoter::DELETE:
                return $dashboard && $this->canEditDashboard($dashboard, $user) && !$dashboard->isDefault();
            default:
                return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForUser($attribute, PermissionGroupContext $context, Person $user)
    {
        // no access for now
        return false;
    }
}
