<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use DeskPRO\Bundle\AppBundle\Notification\Event\People\AgentStatusChangedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;

/**
 * Class AgentStatusChangedMessageGenerator.
 */
class AgentStatusChangedMessageGenerator extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     */
    public function createMessages(SystemEventInterface $event)
    {
        $messages = [];

        $onlineForChatAgents = $this->em->getRepository(Person::class)->getActiveAgentIdsForUserChat();
        /* @var AgentStatusChangedEvent $event */
        foreach ($this->getTargets() as $target) {
            $messages[] = new ActionAlert(
                $target,
                [
                    'type'      => $event->getEventType(),
                    'agent_ids' => $onlineForChatAgents,
                ],
                $event->getName()
            );
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof AgentStatusChangedEvent;
    }

    protected function getTargets()
    {
        /** @var PersonRepository $repo */
        $repo = $this->em->getRepository(Person::class);

        return $repo->getActiveAgents(true);
    }
}
