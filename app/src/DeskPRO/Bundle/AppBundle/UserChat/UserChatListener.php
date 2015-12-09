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
 * Class UserChatListener.
 */
class UserChatListener implements EventSubscriberInterface
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
            UserChatEvent::EVENT_NAME => 'onSystemMessage',
        ];
    }

    /**
     * Sends chat system message to a conversation.
     *
     * @param UserChatEvent $event
     */
    public function onSystemMessage(UserChatEvent $event)
    {
        $conversation = $event->getConversation();
        $phrase_id    = $event->getType();
        $params       = $event->getParams();
        $metadata     = $event->getMetadata();

        $content  = array_merge($params, ['phrase_id' => $phrase_id]);
        $metadata = array_merge($content, $metadata);

        $chat_message = new ChatMessage();
        $chat_message
            ->setIsSys(true)
            ->setContent(json_encode($content))
            ->setMetadata($metadata)
            ->setIsUserHidden($this->isUserHiddenMessage($event))
            ->setIsHtml($this->isHtmlMessage($event))
        ;

        $conversation->addMessage($chat_message);

        $this->em->persist($conversation);
        $this->em->flush();

        $channel = $this->getBaseChannel($event);
        if ($channel) {
            $event->getDispatcher()->dispatch(
                ClientMessageEvent::SEND,
                new ClientMessageEvent($channel, $chat_message)
            );
        }

        $channel_name = $chat_message->getIsUserHidden()
            ? ClientMessageEvent::CHANNEL_CHAT_NEW_MESSAGE_HIDDEN
            : ClientMessageEvent::CHANNEL_CHAT_NEW_MESSAGE;

        $conversation_channel = $conversation->getChannelId($channel_name);
        $event->getDispatcher()->dispatch(
            ClientMessageEvent::SEND,
            new ClientMessageEvent($conversation_channel, $chat_message)
        );
    }

    /**
     * Returns client message channel for chat system message.
     *
     * @param UserChatEvent $event
     *
     * @return string
     */
    protected function getBaseChannel(UserChatEvent $event)
    {
        switch ($event->getType()) {
            case UserChatEvent::TYPE_STARTED:
            case UserChatEvent::TYPE_USER_RETURNED:
                return ClientMessageEvent::CHANNEL_CHAT_NEW;
            case UserChatEvent::TYPE_ENDED:
            case UserChatEvent::TYPE_END_BY:
            case UserChatEvent::TYPE_END_BY_USER:
                return ClientMessageEvent::CHANNEL_CHAT_ENDED;
            case UserChatEvent::TYPE_SET_DEPARTMENT:
                return ClientMessageEvent::CHANNEL_CHAT_DEPARTMENT_CHANGE;
            case UserChatEvent::TYPE_ASSIGNED:
                return ClientMessageEvent::CHANNEL_CHAT_REASSIGNED;
            case UserChatEvent::TYPE_UNASSIGNED:
                return ClientMessageEvent::CHANNEL_CHAT_UNASSIGNED;
        }

        return '';
    }

    /**
     * Is html sys message.
     *
     * @param UserChatEvent $event
     *
     * @return bool
     */
    protected function isHtmlMessage(UserChatEvent $event)
    {
        return in_array($event->getType(), [
            UserChatEvent::TYPE_USER_TRACK,
        ]);
    }

    /**
     * Hide user in sys message.
     *
     * @param UserChatEvent $event
     *
     * @return bool
     */
    protected function isUserHiddenMessage(UserChatEvent $event)
    {
        return in_array($event->getType(), [
            UserChatEvent::TYPE_STARTED,
            UserChatEvent::TYPE_USER_TRACK,
        ]);
    }
}
