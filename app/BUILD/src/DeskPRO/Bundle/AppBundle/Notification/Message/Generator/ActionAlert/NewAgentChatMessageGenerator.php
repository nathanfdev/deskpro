<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class NewAgentChatMessageGenerator.
 */
class NewAgentChatMessageGenerator extends AbstractAgentChatMessageGenerator
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     * @param Serializer            $serializer
     */
    public function __construct(EntityManager $em, TokenStorageInterface $tokenStorage, Serializer $serializer)
    {
        parent::__construct($em, $tokenStorage);
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     *
     * @param NewMessageEvent $event
     */
    public function createMessages(SystemEventInterface $event)
    {
        $messages = [];
        foreach ($this->getTargets($event) as $target) {
            $messages[] = new ActionAlert($target->getId(), $this->getData($event), $event->getName());
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof NewMessageEvent;
    }

    /**
     * @param NewMessageEvent $event
     *
     * @return AgentChatMessage
     */
    protected function getData(NewMessageEvent $event)
    {
        return $this->serializer->toArray(
            new ApiWrapper($this->getChatMessage($event)),
            new SideloadSerializationContext(['agent_chat', 'participants', 'department', 'agent_team'])
        );
    }
}
