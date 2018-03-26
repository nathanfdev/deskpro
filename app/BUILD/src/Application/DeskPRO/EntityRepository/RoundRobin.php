<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\RoundRobinAgent as RoundRobinAgentEntity;

class RoundRobin extends AbstractEntityRepository
{
    /** @var array|null */
    protected $availableAgents = null;

    /**
     * @param \Application\DeskPRO\Entity\RoundRobin $robin
     * @param array                                  $agents
     */
    public function setAgents(\Application\DeskPRO\Entity\RoundRobin $robin, array $agents = [])
    {
        $robin->agents->clear();
        $this->_em->flush();
        $sort = 0;

        foreach ($agents as $agentData) {
            $agentRef        = new RoundRobinAgentEntity();
            $agentRef->robin = $robin;
            $agentRef->agent = $this->_em->getReference('DeskPRO:Person', $agentData['id']);
            $this->_em->persist($agentRef);
            $agentRef['sort'] = ++$sort;
            $robin->agents->add($agentRef);
        }

        $this->_em->flush();
    }
}
