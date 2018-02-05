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

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\Reports;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboard;
use Application\DeskPRO\Entity\ReportDashboardPermission;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\PermissionGroupEntityVoterInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class AbstractReportDashboardVoter.
 */
abstract class AbstractReportDashboardVoter implements PermissionGroupEntityVoterInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param ReportDashboard $entity
     * @param Person          $user
     *
     * @return bool
     */
    protected function canViewDashboard(ReportDashboard $entity, Person $user)
    {
        return $this->checkViewPermission($entity, $user);
    }

    /**
     * @param ReportDashboard $entity
     * @param Person          $user
     *
     * @return bool
     */
    protected function canEditDashboard(ReportDashboard $entity, Person $user)
    {
        if (!$this->checkViewPermission($entity, $user)) {
            return false;
        }

        foreach ($entity->getPermissions() as $permission) {
            if ($permission->getPerson() === $user && $permission->getName() === ReportDashboardPermission::FULL) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ReportDashboard $dashboard
     * @param Person          $user
     *
     * @return bool
     */
    protected function checkViewPermission(ReportDashboard $dashboard, Person $user)
    {
        $result = false;

        if ($dashboard->getPerson() === $user) {
            $result = true;
        }

        foreach ($dashboard->getPermissions() as $permission) {
            if (
                $permission->getPerson() === $user
                || $user->getTeams()->contains($permission->getTeam())
            ) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @param ReportDashboard $entity
     * @param Person          $user
     *
     * @return bool
     */
    protected function canDeleteDashboard(ReportDashboard $entity, Person $user)
    {
        return $this->canEditDashboard($entity, $user) && !$entity->isDefault();
    }
}
