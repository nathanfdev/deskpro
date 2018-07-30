<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Reports;

use Application\DeskPRO\Entity\ReportDashboardPermission;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class DashboardPermissionValidator.
 */
class DashboardPermissionValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (
            // default dashboard may be edited by admins only, no full access for teams, departments or for all
            $value->getDashboard()->isDefault() &&
            (
                ($value->getPerson() && !$value->getPerson()->isAdmin() && !$value->getPerson()->can_reports) ||
                $value->getTeam() || $value->getDepartment() || $value->isGlobalPrivilege()

            ) &&
            $value->getName() === ReportDashboardPermission::FULL
        ) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(DashboardPermission::NO_DEFAULT_DASHBOARD_PERMISSION)
                ->addViolation()
            ;
        }
    }
}
