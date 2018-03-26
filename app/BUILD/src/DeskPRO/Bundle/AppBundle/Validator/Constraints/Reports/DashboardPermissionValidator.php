<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

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
                ($value->getPerson() && !$value->getPerson()->isAdmin()) ||
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
