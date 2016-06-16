<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\UserChat;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;

/**
 * Class UserChatMessages.
 */
class UserChatMessages
{
    /**
     * Create a user message.
     *
     * @param ChatConversation $chat
     * @param string           $content
     *
     * @return ChatMessage
     */
    public static function createUserTextMessage(ChatConversation $chat, $content)
    {
        $chatMessage = new ChatMessage();
        $chatMessage
            ->setOrigin('user')
            ->setAuthor($chat->getPerson())
            ->setContent($content)
            ->setIsUser(true)
            ->setIsHtml(true)
            ->setMetadata([
                'is_html'         => true,
                'is_user_message' => true,
            ])
        ;

        return $chatMessage;
    }

    /**
     * Create an agent message.
     *
     * @param ChatConversation $chat
     * @param string           $content
     *
     * @return ChatMessage
     */
    public static function createAgentTextMessage(ChatConversation $chat, $content)
    {
        $chatMessage = new ChatMessage();
        $chatMessage
            ->setOrigin('agent')
            ->setAuthor($chat->getAgent())
            ->setContent($content)
            ->setIsUser(false)
            ->setIsHtml(true)
            ->setMetadata([
                'is_html'         => true,
                'is_user_message' => false,
            ])
        ;

        return $chatMessage;
    }

    /**
     * Create a chat system message.
     *
     * @param string        $eventName
     * @param UserChatEvent $event
     *
     * @return ChatMessage
     */
    public static function createSysMessage($eventName, UserChatEvent $event)
    {
        $params   = $event->getData();
        $metadata = $event->getMetadata();

        $phraseId = preg_replace('/^user_chat\./', '', $eventName);
        $content  = array_merge($params, ['phrase_id' => $phraseId]);
        $metadata = array_merge($content, $metadata);

        $chatMessage = new ChatMessage();
        $chatMessage
            ->setIsSys(true)
            ->setIsUserHidden(self::isUserHiddenSysMessage($eventName))
            ->setIsHtml(self::isHtmlSysMessage($eventName))
            ->setContent(json_encode($content))
            ->setMetadata($metadata)
        ;

        return $chatMessage;
    }

    /**
     * Is html sys message.
     *
     * @param string $eventName
     *
     * @return bool
     */
    public static function isHtmlSysMessage($eventName)
    {
        return in_array($eventName, [
            UserChatEvent::USER_TRACK,
        ]);
    }

    /**
     * Hide user in sys message.
     *
     * @param string $eventName
     *
     * @return bool
     */
    public static function isUserHiddenSysMessage($eventName)
    {
        return in_array($eventName, [
            UserChatEvent::STARTED,
            UserChatEvent::USER_TRACK,
        ]);
    }
}
