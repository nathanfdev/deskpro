<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\ChatRoundRobin as ChatRoundRobinEntity;
use Application\DeskPRO\Entity\ChatRoundRobinAgent as ChatRoundRobinAgentEntity;

class ChatRoundRobin extends AbstractEntityRepository
{
    /** @var array|null */
    protected $availableAgents = null;

    /**
     * @param ChatRoundRobinEntity $robin
     * @param array                $agents
     */
    public function setAgents(ChatRoundRobinEntity $robin, array $agents = [])
    {
        $this->_em->flush();
        $sort = 0;

        foreach ($agents as $agentData) {
            $agentRef        = new ChatRoundRobinAgentEntity();
            $agentRef->robin = $robin;
            $agentRef->agent = $this->_em->getReference('DeskPRO:Person', $agentData['id']);
            $this->_em->persist($agentRef);
            $agentRef['sort'] = ++$sort;
            $robin->agents->add($agentRef);
        }

        $this->_em->flush();
    }

    /**
     * @param ChatRoundRobinEntity $robin
     * @param array                $departments
     */
    public function setDepartments(ChatRoundRobinEntity $robin, array $departments = [])
    {
        $this->_em->flush();

        foreach ($departments as $departmentId) {
            $department = $this->_em->getReference('DeskPRO:Department', $departmentId);
            $robin->departments->add($department);
        }

        $this->_em->flush();
    }

    public function findByDepartment($departmentId)
    {
        if ($departmentId instanceof \Application\DeskPRO\Entity\Department) {
            $departmentId = $departmentId->getId();
        }
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('r')
            ->from(ChatRoundRobinEntity::class, 'r')
            ->join('r.departments', 'd')
            ->where('d.id = ?1')
            ->setParameter(1, $departmentId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
