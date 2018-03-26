<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Chats;

use Application\DeskPRO\Entity\ChatMessage as ChatMessageEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ChatMessage.
 */
class ChatMessage
{
    /**
     * The unique id of chat message.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * The user who sent the message.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $author = null;

    /**
     * Author id (legacy).
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $authorId;

    /**
     * Author type (legacy).
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @return string
     */
    protected $authorType;

    /**
     * The message.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $content;

    /**
     * The HTML message.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $contentHtml;

    /**
     * Conversation id (legacy).
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $conversationId;

    /**
     * Date message was created.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Date message was received.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateReceived = null;

    /**
     * Is this a system message? (ended, joined, etc).
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isSys = false;

    /**
     * Is this an user's message? (send from the widget).
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isUser = false;

    /**
     * Is the message hidden from the user?
     *
     * @var bool
     */
    protected $isUserHidden = false;

    /**
     * Is the content an HTML message?
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isHtml = false;

    /**
     * Additional data.
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $metadata = [];

    /**
     * Constructor.
     *
     * @param ChatMessageEntity $chat
     */
    public function __construct(ChatMessageEntity $chat)
    {
        $this->author         = $chat->getAuthor();
        $this->authorId       = $chat->getAuthorId();
        $this->authorType     = $chat->getAuthorType();
        $this->content        = $chat->getContent();
        $this->contentHtml    = $chat->getContentHtml();
        $this->conversationId = $chat->getConversationId();
        $this->dateCreated    = $chat->getDateCreated();
        $this->dateReceived   = $chat->getDateReceived();
        $this->id             = $chat->getId();
        $this->isHtml         = $chat->isHtml();
        $this->isSys          = $chat->getIsSys();
        $this->isUser         = $chat->getIsUser();
        $this->isUserHidden   = $chat->getIsUserHidden();
        $this->metadata       = $chat->getMetadata();
    }
}
