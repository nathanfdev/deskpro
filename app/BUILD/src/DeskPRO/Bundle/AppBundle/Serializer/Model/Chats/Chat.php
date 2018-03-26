<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Chats;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Chat.
 */
class Chat extends AbstractChat
{
    /**
     * BC property.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $conversationId;

    /**
     * Subject of the chat conversation.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $subject;

    /**
     * BC property.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $subjectLine;

    /**
     * BC copy of agent id.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $agentId = null;

    /**
     * BC property.
     *
     * @JMS\Type("integer")
     * @JMS\Expose()
     *
     * @var string
     */
    protected $departmentId = 0;

    /**
     * BC property.
     *
     * @JMS\Type("string")
     * @JMS\Expose()
     *
     * @var string
     */
    protected $departmentName = '';

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @JMS\Type("custom_data<array>")
     */
    protected $fields;

    /**
     * String array of labels.
     *
     * @JMS\Type("deferred<array<label<Application\DeskPRO\Entity\LabelChatConversation>>>")
     *
     * @var bool
     */
    private $labels;

    /**
     * {@inheritdoc}
     */
    public function __construct(ChatConversation $chat)
    {
        parent::__construct($chat);

        $this->fields = $chat->getCustomData();

        // BC with legacy message format
        $this->conversationId = $chat->getId();
        $this->subject        = $chat->getSubjectLine();
        $this->subjectLine    = $chat->getSubjectLine();
        $this->departmentId   = $chat->getDepartment() ? $chat->getDepartment()->getId() : 0;
        $this->departmentName = $chat->getDepartment() ? $chat->getDepartment()->getFullTitle() : '';
        $this->agentId        = $chat->getAgentId();
    }

    /**
     * @param CallbackDeferredProperty $labels
     *
     * @return $this
     */
    public function setLabels($labels = null)
    {
        $this->labels = $labels;

        return $this;
    }
}
