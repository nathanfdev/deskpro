<?php

namespace DeskPRO\Bundle\AppBundle\UserChat;

use DeskPRO\Bundle\AppBundle\EventListener\ClientMessage\ClientMessageEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UserChatSystemListener.
 */
class UserChatSystemListener implements EventSubscriberInterface
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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            UserChatEvent::STARTED        => 'onChatEvent',
            UserChatEvent::USER_RETURNED  => 'onChatEvent',
            UserChatEvent::ENDED          => 'onChatEvent',
            UserChatEvent::END_BY         => 'onChatEvent',
            UserChatEvent::END_BY_USER    => 'onChatEvent',
            UserChatEvent::SET_DEPARTMENT => 'onChatEvent',
            UserChatEvent::ASSIGNED       => 'onChatEvent',
            UserChatEvent::UNASSIGNED     => 'onChatEvent',
        ];
    }

    /**
     * Sends chat system message to a conversation.
     *
     * @param UserChatEvent $event
     */
    public function onChatEvent(UserChatEvent $event)
    {
        $conversation = $event->getChat();
        $chatMessage  = UserChatMessages::createSysMessage($event->getName(), $event);
        $conversation->addMessage($chatMessage);

        $this->em->persist($conversation);
        $this->em->flush();

        $channelType = $chatMessage->getIsUserHidden() ? 'newmessage_hidden' : 'newmessage';
        $channel     = $conversation->getChannelId($channelType);

        $event->getDispatcher()->dispatch(ClientMessageEvent::SEND, new ClientMessageEvent($channel, $chatMessage));
    }
}
