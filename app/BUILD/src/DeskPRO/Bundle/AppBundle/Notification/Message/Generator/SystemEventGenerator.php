<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator;

use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class SystemEventGenerator.
 */
class SystemEventGenerator extends AbstractGenerator
{
    /**
     * @var AgentDataService
     */
    protected $agentDataService;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     * @param AgentDataService      $agentDataService
     */
    public function __construct(
        EntityManager         $em,
        TokenStorageInterface $tokenStorage,
        AgentDataService      $agentDataService
    ) {
        parent::__construct($em, $tokenStorage);
        $this->agentDataService = $agentDataService;
    }

    /**
     * {@inheritdoc}
     */
    public function createMessages(SystemEventInterface $event)
    {
        $event->getName();
        /* @var LegacySystemEvent $event */
        $messages = [];
        foreach ($this->getTarget($event) as $agent) {
            $messages[] = new ActionAlert((int) $agent, $event->getData(), $event->getName());
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return get_class($event) === LegacySystemEvent::class;
    }

    /**
     * @param LegacySystemEvent $event
     *
     * @return array|\int[]
     */
    protected function getTarget(LegacySystemEvent $event)
    {
        $targets = $event->getTargets();
        if (!$targets) {
            $targets = $this->agentDataService->getOnlineAgentIds();
        }

        return array_diff($targets, $event->getExcludeTargets());
    }
}
