<?php

namespace DeskPRO\Bundle\ReportBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboardWidget;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class ReportDashboardWidgetVoter.
 */
class ReportDashboardWidgetVoter extends AbstractReportDashboardVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return ReportDashboardWidget::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        /** @var ReportDashboardWidget $widget */
        $widget = $context->getParent();

        if ($widget) {
            $report    = $widget->getReport();
            $dashboard = $report->getDashboard();
        } else {
            $dashboard = null;
        }

        switch ($attribute) {
            case PermissionGroupVoter::VIEW:
                return $dashboard && $this->canViewDashboard($dashboard, $user);
            case PermissionGroupVoter::MODIFY:
            case PermissionGroupVoter::DELETE:
                return $this->canEditDashboard($dashboard, $user) && !$dashboard->isDefault();
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
