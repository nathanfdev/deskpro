<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\EntityRepository\AgentTeam as AgentTeamRepository;
use Doctrine\ORM\EntityRepository;

class AgentChatParticipantRepository extends EntityRepository
{
    public function findChatsIds(PersonEntity $person, $department_ids)
    {
        /* @var AgentTeamRepository $teamRepo */
        $qb = $this->createQueryBuilder('acp')
            ->select('IDENTITY(acp.chat) as agent_chat_id')
            ->andWhere('acp.person = :person')
            ->orWhere('acp.team IN (:agent_team_id)')
            ->orWhere('acp.department IN (:department_id)')
            ->setParameters(
                [
                    'person'        => $person->getId(),
                    'agent_team_id' => $person->getTeamIds(),
                    'department_id' => $department_ids,
                ]
            );

        $result = $qb->getQuery()->getScalarResult();
        $ids    = array_map('current', $result);

        return array_unique($ids);
    }
}
