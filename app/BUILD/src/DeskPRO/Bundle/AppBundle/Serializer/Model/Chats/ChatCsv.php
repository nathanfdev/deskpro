<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Chats;

use Application\DeskPRO\Entity\ChatConversation;
use JMS\Serializer\Annotation as JMS;

class ChatCsv extends Chat
{
    /**
     * Author's name.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $person;

    /**
     * Author's name.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $agent;

    /**
     * Constructor.
     *
     * @param ChatConversation $chat
     */
    public function __construct(ChatConversation $chat)
    {
        parent::__construct($chat);
        $this->person = $chat->getPersonName().' '.$chat->getPersonEmail();
        $this->agent  = $chat->getAgent() ? $chat->getAgent()->getName().' '.$chat->getAgent()->getPrimaryEmail() : '';
    }
}
