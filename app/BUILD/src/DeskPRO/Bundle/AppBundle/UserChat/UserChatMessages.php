<?php

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
            ->setPersonName($chat->getPersonName())
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
     * @param ChatConversation $chat
     * @param string           $url
     * @param string           $title
     *
     * @return ChatMessage
     */
    public static function createUserTrackMessage(ChatConversation $chat, $url, $title = null)
    {
        $phraseId = 'msg_new_user_track';

        $urlShow = preg_replace('#^https?://(www\.)?#i', '', $url);
        if (strlen($urlShow) > 50) {
            $urlShow = substr($urlShow, 0, 50).'...';
        }

        $url     = htmlspecialchars($url);
        $urlShow = htmlspecialchars($urlShow);

        if ($title) {
            $urlShow = sprintf('%s, %s', $urlShow, $title);
        }

        $label = "<a href=\"$url\" target=\"_blank\" title=\"$url\">$urlShow</a>";

        $metadata = [
            'phrase_id' => $phraseId,
            'label'     => $label,
            'url'       => $url,
        ];

        $chatMessage = new ChatMessage();
        $chatMessage
            ->setIsSys(true)
            ->setIsUserHidden(true)
            ->setIsHtml(true)
            ->setContent(json_encode($metadata))
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
