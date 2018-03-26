<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Chats\ChatMessage;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;

class AgentNewChatMessage extends EmailBaseType
{
    /**
     * The chat message.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Chats\ChatMessage")
     *
     * @var ChatMessage
     */
    protected $chatMessage;

    /**
     * The author of the message.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $author;

    protected $templateFile = 'emails_agent:new_agent_chat_message.html.twig';

    public function __construct(ChatMessage $chatMessage, $author)
    {
        $this->chatMessage = $chatMessage;
        $this->author      = $author;
    }
}
