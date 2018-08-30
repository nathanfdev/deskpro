<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Bundle\AppBundle\Notification\Event\ExternalEvent\PopupEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class PersonMessageGenerator.
 */
class PopupEventGenerator extends AbstractGenerator
{
    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     * @param AgentDataService      $agentDataService
     */
    public function __construct(
        EntityManager         $em,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($em, $tokenStorage);
    }

    /**
     * {@inheritdoc}
     */
    public function createMessages(SystemEventInterface $event)
    {
        $event->getName();
        $messages = [];
        foreach ([1] as $agent) {
            $messages[] = new ActionAlert($agent, $this->getData($event), $event->getName());
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof PopupEvent;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return array
     */
    public function getData(SystemEventInterface $event)
    {
        if ($event instanceof PopupEvent) {
            return array_merge(
                [
                    'uuid'   => $event->getUuid(),
                    'action' => $event->getAction(),
                ],
                $event->getData()
            );
        }

        return [];
    }
}
