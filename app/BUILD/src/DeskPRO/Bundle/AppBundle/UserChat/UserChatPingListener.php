<?php

namespace DeskPRO\Bundle\AppBundle\UserChat;

use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatEvent;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatMessageEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UserChatPingListener.
 */
class UserChatPingListener implements EventSubscriberInterface
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
            UserChatEvent::STARTED       => 'onPing',
            UserChatEvent::USER_RETURNED => 'onPing',
            UserChatEvent::SEND_MESSAGE  => 'onPing',
            UserChatEvent::USER_TYPING   => 'onPing',
            UserChatEvent::ACK_MESSAGES  => 'onPing',
            UserChatEvent::POLLING       => 'onPing',
            ChatEvent::EVENT_NAME        => 'onMessengerPing',
            ChatMessageEvent::EVENT_NAME => 'onMessengerPing',
        ];
    }

    /**
     * @param UserChatEvent $event
     */
    public function onPing(UserChatEvent $event)
    {
        $this->em->getConnection()->insert('chat_conversation_pings', [
            'chat_id'   => $event->getChat()->getId(),
            'ping_time' => time(),
        ]);
    }

    /**
     * @param ChatEvent $event
     */
    public function onMessengerPing(ChatEvent $event)
    {
        $this->em->getConnection()->insert('chat_conversation_pings', [
            'chat_id'   => $event->getChatId(),
            'ping_time' => time(),
        ]);
    }
}
