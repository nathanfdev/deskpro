<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAgentTarget;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class VoiceAgentTargetListener.
 */
class VoiceAgentTargetListener
{
    /**
     * @var EntityManager
     */
    private $em;

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
     * @ORM\PreFlush()
     *
     * @param VoiceAgentTarget $target
     */
    public function ensureAgentVoiceEnabled(VoiceAgentTarget $target)
    {
        $agent = $target->getAgent();
        if (!$agent) {
            return;
        }

        $agentData = $agent->getAgentData();
        if (!$agentData) {
            $agentData = new AgentData();
            $agent->setAgentData($agentData);
        }

        $agentData->setIsVoiceEnabled(true);
        $agentData->setOutboundCallsEnabled(true);

        $this->em->persist($agent);

        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSet($this->em->getClassMetadata(get_class($agentData)), $agentData);
    }
}
