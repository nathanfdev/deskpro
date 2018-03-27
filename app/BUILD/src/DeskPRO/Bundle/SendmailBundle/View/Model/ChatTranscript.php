<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Chats\Chat;
use JMS\Serializer\Annotation as JMS;

class ChatTranscript extends EmailBaseType
{
    /**
     * The chat conversation.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Chats\Chat")
     *
     * @var Chat
     */
    protected $convo;

    /**
     * The chat messages.
     *
     * @JMS\Type("array<Application\DeskPRO\Entity\ChatMessage>")
     *
     * @var ChatMessage[]
     */
    protected $convoMessages;

    protected $templateFile = 'emails_user:chat_transcript.html.twig';

    /**
     * ChatTranscript constructor.
     *
     * @param $chat
     * @param $convoMessages
     */
    public function __construct($chat, $convoMessages)
    {
        $this->convo         = $chat;
        $this->convoMessages = $convoMessages;
    }
}
