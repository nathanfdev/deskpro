<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Chats;

use Application\DeskPRO\Entity\ChatConversation;
use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetChat.
 */
class WidgetChat extends AbstractChat
{
    /**
     * Session auth id.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $authId;

    /**
     * Constructor.
     *
     * @param ChatConversation $chat
     */
    public function __construct(ChatConversation $chat)
    {
        parent::__construct($chat);

        $this->authId = $chat->getAuthId();
    }
}
