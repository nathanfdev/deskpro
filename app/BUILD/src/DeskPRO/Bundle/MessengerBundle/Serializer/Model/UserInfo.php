<?php

namespace DeskPRO\Bundle\MessengerBundle\Serializer\Model;

use Application\DeskPRO\Entity\ChatConversation;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class UserInfo.
 */
class UserInfo implements MessengerModelInterface
{
    /**
     * @var string
     */
    private $visitorId;

    /**
     * @var ArrayCollection
     */
    private $chats;

    /**
     * UserInfo constructor.
     *
     * @param string $visitorId
     */
    public function __construct($visitorId)
    {
        $this->visitorId = $visitorId;
        $this->chats     = new ArrayCollection();
    }

    public function toArray()
    {
        return [
            'visitor_id' => $this->visitorId,
            'chats'      => $this->chats->toArray(),
        ];
    }

    /**
     * @param ChatConversation $chatConversation
     *
     * @return $this
     */
    public function addChat(ChatConversation $chatConversation)
    {
        $this->chats->add($chatConversation);

        return $this;
    }

    /**
     * @param array $chats
     *
     * @return $this
     */
    public function addChats(array $chats)
    {
        foreach ($chats as $chat) {
            $this->addChat($chat);
        }

        return $this;
    }
}
