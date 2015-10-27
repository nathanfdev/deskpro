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
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person as PersonEntity;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat as AgentChatEntity;
use Doctrine\ORM\EntityRepository;

class AgentChat extends EntityRepository
{
    /**
     * @param PersonEntity $person
     *
     * @return AgentChatEntity[]
     */
    public function findAllPersonChats(PersonEntity $person)
    {
    }

    /**
     * @param int $agent_id
     * @param int $my_id
     *
     * @return mixed
     */
    public function findChatWithAgent($agent_id, $my_id)
    {
        $qb = $this->createQueryBuilder('ac');
        $qb->innerJoin('App:AgentChatParticipant', 'acp', 'WITH', 'ac.id = acp.chat')
            ->innerJoin('App:AgentChatParticipant', 'acp2', 'WITH', 'ac.id = acp.chat')
            ->andWhere('acp.person = :agent_id')
            ->andWhere('acp2.person = :my_id')
            ->andWhere('ac.type = :type')
            ->setParameter('agent_id', $agent_id)
            ->setParameter('my_id', $my_id)
            ->setParameter('type', 'agent');

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    /**
     * @param int $team_id
     *
     * @return mixed
     */
    public function findTeamChat($team_id)
    {
        $qb = $this->createQueryBuilder('ac');
        $qb->innerJoin('App:AgentChatParticipant', 'acp', 'WITH', 'ac.id = acp.chat')
            ->andWhere('acp.team = :team_id')
            ->andWhere('ac.type = :type')
            ->setParameter('team_id', $team_id)
            ->setParameter('type', 'team');

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    /**
     * @param int $department_id
     *
     * @return mixed
     */
    public function findDepartmentChat($department_id)
    {
        $qb = $this->createQueryBuilder('ac');
        $qb->innerJoin('App:AgentChatParticipant', 'acp', 'WITH', 'ac.id = acp.chat')
            ->andWhere('acp.department = :department_id')
            ->andWhere('ac.type = :type')
            ->setParameter('department_id', $department_id)
            ->setParameter('type', 'department');

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    public function findEveryoneChat()
    {
        $qb = $this->createQueryBuilder('ac');
        $qb->andWhere('ac.type = :type')
            ->setParameter('type', 'everyone');

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    public function findAllChats(array $ids, array $order)
    {
        $qb = $this->createQueryBuilder('ac');
        $qb->andWhere('ac.id IN (:ids)')
            ->orWhere('ac.type = :type')
            ->setParameter('ids', $ids)
            ->setParameter('type', 'everyone');

        foreach ($order as $sort => $direction) {
            $qb->addOrderBy('ac.'.$sort, $direction);
        }

        $results = $qb->getQuery()->getResult();

        return $results;
    }
}
