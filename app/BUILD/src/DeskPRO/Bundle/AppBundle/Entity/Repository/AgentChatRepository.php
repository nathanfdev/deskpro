<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat as AgentChatEntity;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatParticipant;
use Doctrine\ORM\EntityRepository;

/**
 * Class AgentChatRepository.
 */
class AgentChatRepository extends EntityRepository
{
    /**
     * @param Person $me
     *
     * @return AgentChatEntity[]
     */
    public function findGroupChats(Person $me)
    {
        $qb = $this->createQueryBuilder('ac');
        $qb
            ->innerJoin(AgentChatParticipant::class, 'acp', 'WITH', 'ac.id = acp.chat')
            ->andWhere('acp.person = :me')
            ->andWhere('ac.type = :type')
            ->setParameter('me', $me)
            ->setParameter('type', 'group');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Person $agent
     * @param Person $me
     *
     * @return AgentChat
     */
    public function findChatWithAgent(Person $agent, Person $me)
    {
        $qb = $this->createQueryBuilder('ac');
        $qb
            ->innerJoin(AgentChatParticipant::class, 'acp', 'WITH', 'ac.id = acp.chat')
            ->innerJoin(AgentChatParticipant::class, 'acp2', 'WITH', 'ac.id = acp2.chat')
            ->andWhere('acp.person = :agent')
            ->andWhere('acp2.person = :me')
            ->andWhere('ac.type = :type')
            ->setParameter('agent', $agent)
            ->setParameter('me', $me)
            ->setParameter('type', 'agent')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param AgentTeam $team
     *
     * @return AgentChat
     */
    public function findTeamChat(AgentTeam $team)
    {
        $qb = $this->createQueryBuilder('ac');
        $qb
            ->innerJoin(AgentChatParticipant::class, 'acp', 'WITH', 'ac.id = acp.chat')
            ->andWhere('acp.team = :team_id')
            ->andWhere('ac.type = :type')
            ->setParameter('team_id', $team)
            ->setParameter('type', 'team')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Department $department
     *
     * @return AgentChat
     */
    public function findDepartmentChat(Department $department)
    {
        $qb = $this->createQueryBuilder('ac');
        $qb
            ->innerJoin(AgentChatParticipant::class, 'acp', 'WITH', 'ac.id = acp.chat')
            ->andWhere('acp.department = :department_id')
            ->andWhere('ac.type = :type')
            ->setParameter('department_id', $department)
            ->setParameter('type', 'department')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return AgentChat
     */
    public function findEveryoneChat()
    {
        $qb = $this->createQueryBuilder('ac');
        $qb
            ->andWhere('ac.type = :type')
            ->setParameter('type', 'everyone')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllChats(array $ids, array $order)
    {
        $qb = $this->createQueryBuilder('ac');
        $qb
            ->andWhere('ac.id IN (:ids)')
            ->orWhere('ac.type = :type')
            ->setParameter('ids', $ids)
            ->setParameter('type', 'everyone')
        ;

        foreach ($order as $sort => $direction) {
            $qb->addOrderBy('ac.'.$sort, $direction);
        }

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    public function findGroupChat($participants)
    {
        $qb = $this->createQueryBuilder('ac');
        $qb
            ->innerJoin(AgentChatParticipant::class, 'acp', 'WITH', 'ac.id = acp.chat')
            ->andWhere('ac.type = :type')
            ->setParameter('type', 'group')
            ->groupBy('ac.id')
            ->having('COUNT(acp.person) = :count')
            ->andHaving('SUM(CASE WHEN acp.person IN (:participants) THEN 1 ELSE 0 END) = :count2')
            ->setParameter('participants', $participants)
            ->setParameter('count', count($participants))
            ->setParameter('count2', count($participants))
            ->orderBy('ac.id', 'DESC')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
