<?php

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
            if (
                ($permission->getPerson() === $user || (!$permission->getPerson() && !$permission->getTeam() && !$permission->getDepartment()))
                && $permission->getName() === ReportDashboardPermission::FULL) {
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
                || (!$permission->getPerson() && !$permission->getTeam() && !$permission->getDepartment())
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
