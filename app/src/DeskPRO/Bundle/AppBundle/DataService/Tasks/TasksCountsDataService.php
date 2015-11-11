<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\DataService\Tasks;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class TasksCountsDataService.
 */
class TasksCountsDataService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Person
     */
    private $user;

    /**
     * @param EntityManager $em
     * @param TokenStorage  $tokenStorage
     */
    public function __construct(EntityManager $em, TokenStorage $tokenStorage)
    {
        $this->em   = $em;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @return int
     */
    public function getAllRemainingCount()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('COUNT(t.id)')
            ->from('App:Task', 't')
            ->where($qb->expr()->eq('t.is_done', 0))
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function getMyRemainingCount()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('COUNT(t.id)')
            ->from('App:Task', 't')
            ->join('t.assigned', 'ta')
            ->where(
                $qb->expr()->eq('ta.person', $this->user->getId()),
                $qb->expr()->eq('t.is_done', 0)
            )
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function getTeamRemainingCount()
    {
        $this->user->loadHelper('AgentTeam');
        $team_ids = $this->user->getAgentTeamIds();
        if (!$team_ids) {
            return 0;
        }

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('COUNT(t.id)')
            ->from('App:Task', 't')
            ->join('t.assigned', 'ta')
            ->where(
                $qb->expr()->in('ta.team', $team_ids),
                $qb->expr()->eq('t.is_done', 0)
            )
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function getDepartmentRemainingCount()
    {
        $this->user->loadHelper('AgentPermissions');
        $department_ids = $this->user->getAllowedDepartments();
        if (!$department_ids) {
            return 0;
        }

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('COUNT(t.id)')
            ->from('App:Task', 't')
            ->join('t.assigned', 'ta')
            ->where(
                $qb->expr()->in('ta.department', $department_ids),
                $qb->expr()->eq('t.is_done', 0)
            )
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function getDelegatedRemainingCount()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('COUNT(t.id)')
            ->from('App:Task', 't')
            ->join('t.assigned', 'ta')
            ->where(
                $qb->expr()->neq('ta.person', $this->user->getId()),
                $qb->expr()->eq('t.creator', $this->user->getId()),
                $qb->expr()->eq('t.is_done', 0)
            )
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function getUnassignedRemainingCount()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('COUNT(t.id)')
            ->from('App:Task', 't')
            ->leftJoin('t.assigned', 'ta')
            ->where(
                $qb->expr()->isNull('ta.person'),
                $qb->expr()->isNull('ta.team'),
                $qb->expr()->isNull('ta.department'),
                $qb->expr()->eq('t.is_done', 0)
            )
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
