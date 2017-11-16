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
