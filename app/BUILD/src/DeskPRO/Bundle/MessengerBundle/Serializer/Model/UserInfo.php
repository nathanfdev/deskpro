<?php

namespace DeskPRO\Bundle\MessengerBundle\Serializer\Model;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
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
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var int
     */
    private $personId;

    /**
     * UserInfo constructor.
     *
     * @param string $visitorId
     * @param Person $person
     */
    public function __construct($visitorId, Person $person = null)
    {
        $this->visitorId = $visitorId;
        $this->chats     = new ArrayCollection();
        if ($person) {
            $this->name     = $person->getDisplayName();
            $this->personId = $person->getId();
            $this->email    = $person->getEmailAddress();
        }
    }

    public function toArray()
    {
        return [
            'visitor_id' => $this->visitorId,
            'chats'      => $this->chats->toArray(),
            'person_id'  => $this->personId,
            'email'      => $this->email,
            'name'       => $this->name,
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
