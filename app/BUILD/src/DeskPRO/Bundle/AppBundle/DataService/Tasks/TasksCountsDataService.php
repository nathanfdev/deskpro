<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DataService\Tasks;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject;
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
    protected $em;

    /**
     * @var Person
     */
    protected $user;

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
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBaseQueryBuilder()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->from(Task::class, 't');

        return $qb;
    }

    /**
     * @return int
     */
    public function getAllCount()
    {
        $qb = $this->getBaseQueryBuilder();
        $qb
            ->select('COUNT(t.id)')
            ->andWhere('t.for_del <> 1')
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function getMyCount()
    {
        $qb = $this->getBaseQueryBuilder();
        $qb
            ->select('COUNT(t.id)')
            ->join('t.assigned', 'ta')
            ->andWhere('t.for_del <> 1')
            ->andWhere($qb->expr()->eq('ta.person', $this->user->getId()))
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function getTeamCount()
    {
        $this->user->loadHelper('AgentTeam');
        $ids = $this->user->getAgentTeamIds();
        if (!$ids) {
            return 0;
        }

        $qb = $this->getBaseQueryBuilder();
        $qb
            ->select('COUNT(t.id)')
            ->join('t.assigned', 'ta')
            ->andWhere('t.for_del <> 1')
            ->andWhere($qb->expr()->in('ta.team', $ids))
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function getDepartmentCount()
    {
        $this->user->loadHelper('AgentPermissions');
        $ids = $this->user->getAllowedDepartments();
        if (!$ids) {
            return 0;
        }

        $qb = $this->getBaseQueryBuilder();
        $qb
            ->select('COUNT(t.id)')
            ->join('t.assigned', 'ta')
            ->andWhere('t.for_del <> 1')
            ->andWhere($qb->expr()->in('ta.department', $ids))
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function getDelegatedCount()
    {
        $qb = $this->getBaseQueryBuilder();
        $qb
            ->select('COUNT(t.id)')
            ->join('t.assigned', 'ta')
            ->andWhere('t.for_del <> 1')
            ->andWhere('t.creator = :user')
            ->andWhere('ta.person <> :user OR ta.person IS NULL')
            ->setParameter('user', $this->user->getId())
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function getUnassignedCount()
    {
        $qb = $this->getBaseQueryBuilder();
        $qb
            ->select('COUNT(t.id)')
            ->leftJoin('t.assigned', 'ta')
            ->andWhere('t.for_del <> 1')
            ->andWhere(
                $qb->expr()->isNull('ta.person'),
                $qb->expr()->isNull('ta.team'),
                $qb->expr()->isNull('ta.department')
            )
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function getAgentsCounts()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('p.id, p.name, COALESCE(COUNT(t.id), 0) AS tasks_count')
            ->from(Person::class, 'p')
            ->leftJoin('p.assigned_tasks', 'ta')
            ->leftJoin('ta.task', 't')
            ->where($qb->expr()->eq('p.is_agent', 1))
            ->groupBy('p.id')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getProjectsCounts()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('p.id, p.title, COALESCE(COUNT(t.id), 0) AS tasks_count')
            ->from(TaskProject::class, 'p')
            ->leftJoin('p.tasks', 't')
            ->groupBy('p.id')
        ;

        return $qb->getQuery()->getResult();
    }
}
