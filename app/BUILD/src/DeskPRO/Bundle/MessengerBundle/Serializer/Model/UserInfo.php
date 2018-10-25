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
     * @var int
     */
    private $lastActionAlert;

    /**
     * UserInfo constructor.
     *
     * @param string $visitorId
     * @param int    $lastActionAlert
     */
    public function __construct($visitorId, $lastActionAlert)
    {
        $this->visitorId       = $visitorId;
        $this->chats           = new ArrayCollection();
        $this->lastActionAlert = $lastActionAlert;
    }

    public function toArray()
    {
        return [
            'visitor_id'        => $this->visitorId,
            'chats'             => $this->chats->toArray(),
            'last_action_alert' => $this->lastActionAlert,
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
