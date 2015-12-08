<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\UserChat;

use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\AppBundle\EventListener\ClientMessage\ClientMessageEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SystemMessageListener.
 */
class SystemEventListener implements EventSubscriberInterface
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
            UserChatEvent::SYSTEM_EVENT => 'onSystemMessage',
        ];
    }

    /**
     * Sends chat system message to a conversation.
     *
     * @param UserChatSystemEvent $event
     */
    public function onSystemMessage(UserChatSystemEvent $event)
    {
        $conversation = $event->getConversation();
        $phrase_id    = $event->getType();
        $params       = $event->getParams();
        $metadata     = $event->getMetadata();

        $chat_message = new ChatMessage();
        $chat_message
            ->setIsSys(true)
            ->setContent(json_encode(array_merge($params, [
                'phrase_id' => $phrase_id,
            ])))
            ->setMetadata(array_merge($params, $metadata))
        ;

        $conversation->addMessage($chat_message);

        $this->em->persist($conversation);
        $this->em->flush();

        $channel = $this->getBaseChannel($event);
        if ($channel) {
            $event->getDispatcher()->dispatch(
                ClientMessageEvent::SEND_MESSAGE,
                new ClientMessageEvent($channel, $chat_message)
            );
        }

        $conversation_channel = $conversation->getChannelId(ClientMessageEvent::CHANNEL_CHAT_NEW_MESSAGE);
        $event->getDispatcher()->dispatch(
            ClientMessageEvent::SEND_MESSAGE,
            new ClientMessageEvent($conversation_channel, $chat_message)
        );
    }

    /**
     * Returns client message channel for chat system message.
     *
     * @param UserChatSystemEvent $event
     *
     * @return string
     */
    protected function getBaseChannel(UserChatSystemEvent $event)
    {
        switch ($event->getType()) {
            case UserChatSystemEvent::TYPE_STARTED:
            case UserChatSystemEvent::TYPE_USER_RETURNED:
                return ClientMessageEvent::CHANNEL_CHAT_NEW;
            case UserChatSystemEvent::TYPE_ENDED:
            case UserChatSystemEvent::TYPE_END_BY:
            case UserChatSystemEvent::TYPE_END_BY_USER:
                return ClientMessageEvent::CHANNEL_CHAT_ENDED;
            case UserChatSystemEvent::TYPE_SET_DEPARTMENT:
                return ClientMessageEvent::CHANNEL_CHAT_DEPARTMENT_CHANGE;
            case UserChatSystemEvent::TYPE_ASSIGNED:
                return ClientMessageEvent::CHANNEL_CHAT_REASSIGNED;
            case UserChatSystemEvent::TYPE_UNASSIGNED:
                return ClientMessageEvent::CHANNEL_CHAT_UNASSIGNED;
        }

        return '';
    }
}
