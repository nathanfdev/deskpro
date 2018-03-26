<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Widget;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetChatDemoState.
 */
class WidgetChatDemoState
{
    /**
     * @var ChatConversation
     *
     * @JMS\Type("Application\DeskPRO\Entity\ChatConversation")
     */
    private $info;

    /**
     * @var ChatMessage[]
     *
     * @JMS\Type("array<Application\DeskPRO\Entity\ChatMessage>")
     */
    private $messages;

    /**
     * Constructor.
     *
     * @param ChatConversation $chat
     */
    public function __construct(ChatConversation $chat)
    {
        $this->info     = $chat;
        $this->messages = $chat->getCreatedMessages();
    }
}
