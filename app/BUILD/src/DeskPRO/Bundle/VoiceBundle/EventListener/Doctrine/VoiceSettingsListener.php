<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Permission;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class VoiceSettingsListener.
 */
class VoiceSettingsListener
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManager $em, EventDispatcherInterface $eventDispatcher)
    {
        $this->em              = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Send notification to real-time update voice agents online count.
     *
     * @ORM\PreFlush()
     *
     * @param AgentData $agentData
     */
    public function sendVoiceAgentCallsEnabledNotification(AgentData $agentData)
    {
        $agent = $agentData->getPerson();
        if (!$agent) {
            return;
        }

        $uow = $this->em->getUnitOfWork();

        $originalAgentData = $uow->getOriginalEntityData($agentData);
        if (!empty($originalAgentData) && $originalAgentData['agentCallsEnabled'] === $agentData->isAgentCallsEnabled()) {
            return;
        }

        $this->eventDispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.calls_enabled',
            [
                'person_id'           => $agent->getId(),
                'agent_calls_enabled' => $agentData->isAgentCallsEnabled(),
            ]
        ));
    }

    /**
     * Force set 'ticket.use' and 'person.use' permissions that are required to use voice.
     *
     * @ORM\PreFlush()
     *
     * @param AgentData $agentData
     */
    public function setRelatedPermissions(AgentData $agentData)
    {
        if (!$agentData->isVoiceEnabled()) {
            return;
        }

        $agent = $agentData->getPerson();
        if (!$agent) {
            return;
        }

        foreach (['agent_people.use', 'agent_tickets.use'] as $permName) {
            if (!$agent->hasPerm($permName)) {
                $permission = new Permission();
                $permission->setPerson($agent);
                $permission->setName($permName);
                $permission->setValue(1);

                $this->em->persist($permission);

                $uow = $this->em->getUnitOfWork();
                $uow->computeChangeSet($this->em->getClassMetadata(get_class($permission)), $permission);
            }
        }
    }
}
