<?php

namespace DeskPRO\Bundle\MessengerBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\MessengerBundle\Mapper\ChatMapper;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatEvent;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatMessageEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ChatMessageGenerator.
 */
class ChatMessageGenerator extends ChatGenerator
{
    /**
     * @var ChatMapper
     */
    protected $chatMapper;

    /**
     * ChatMessageGenerator constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     * @param AvatarResolver        $resolver
     * @param ChatMapper            $mapper
     */
    public function __construct(
        EntityManager $em,
        TokenStorageInterface $tokenStorage,
        AvatarResolver $resolver,
        ChatMapper $mapper
    ) {
        parent::__construct($em, $tokenStorage, $resolver);
        $this->chatMapper = $mapper;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return bool
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof ChatMessageEvent;
    }

    /**
     * @param \DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatEvent $event
     *
     * @return array
     */
    protected function getData(ChatEvent $event)
    {
        /** @var \DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatMessageEvent $event */
        $message = $this->getMessage($event);

        return $this->chatMapper->mapMessageToArray($message);
    }

    /**
     * @param \DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatMessageEvent $event
     *
     * @return ChatMessage
     */
    protected function getMessage(ChatMessageEvent $event)
    {
        /** @var \DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatMessageEvent $event */
        $repository = $this->em->getRepository(ChatMessage::class);

        return $repository->find($event->getMessageId());
    }
}
