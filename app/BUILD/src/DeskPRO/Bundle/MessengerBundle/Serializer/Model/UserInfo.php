<?php

namespace DeskPRO\Bundle\MessengerBundle\Serializer\Model;

use Application\DeskPRO\Entity\ChatConversation;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class UserInfo.
 */
class UserInfo
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $visitorId;

    /**
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\ChatConversation>>")
     *
     * @var ArrayCollection
     */
    private $chats;

    /**
     * @JMS\Type("integer")
     *
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

    /**
     * @return mixed
     */
    public function getVisitorId()
    {
        return $this->visitorId;
    }

    /**
     * @return mixed
     */
    public function getChats()
    {
        return $this->chats;
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
