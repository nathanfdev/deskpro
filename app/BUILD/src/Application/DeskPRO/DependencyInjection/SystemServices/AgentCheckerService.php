<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use DpSys\License;

class AgentCheckerService
{
    /**
     * @var EntityManager
     */
    protected $em;

    public static function create(DeskproContainer $container, array $options = null)
    {
        return new static($container->getEm());
    }

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Can we promote this Person to become an agent?
     *
     * @param Person $person
     *
     * @return bool
     */
    public function addAgentSeat(Person $person)
    {
        // 0 = unlimited
        if (License::getLicense()->getMaxAgents() == 0) {
            return true;
        }

        $active_agent_count = $this->em->getRepository('DeskPRO:Person')->getActiveAgentsCount();
        if ($active_agent_count >= License::getLicense()->getMaxAgents()) {
            return false;
        }

        return true;
    }
}
