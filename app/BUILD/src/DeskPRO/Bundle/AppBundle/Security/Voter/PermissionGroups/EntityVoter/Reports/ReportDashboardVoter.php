<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\Reports;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboard;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class ReportDashboardVoter.
 */
class ReportDashboardVoter extends AbstractReportDashboardVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return ReportDashboard::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        switch ($attribute) {
            case PermissionGroupVoter::VIEW:
                return $this->canViewDashboard($context->getParent(), $user);
            case PermissionGroupVoter::MODIFY:
                return $this->canEditDashboard($context->getParent(), $user);
            case PermissionGroupVoter::DELETE:
                return $this->canDeleteDashboard($context->getParent(), $user);
        }

        return true;
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
