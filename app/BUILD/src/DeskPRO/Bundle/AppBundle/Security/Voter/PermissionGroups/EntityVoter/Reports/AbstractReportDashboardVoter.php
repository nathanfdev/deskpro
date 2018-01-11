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
        $permission = $this->em->getRepository(ReportDashboardPermission::class)->findOneBy([
            'person'    => $user->getId(),
            'dashboard' => $entity,
        ]);

        return $permission !== null;
    }

    /**
     * @param ReportDashboard $entity
     * @param Person          $user
     *
     * @return bool
     */
    protected function canEditDashboard(ReportDashboard $entity, Person $user)
    {
        $permission = $this->em->getRepository(ReportDashboardPermission::class)->findOneBy([
            'person'    => $user->getId(),
            'dashboard' => $entity,
            'name'      => ReportDashboardPermission::FULL,
        ]);

        return $permission !== null;
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
