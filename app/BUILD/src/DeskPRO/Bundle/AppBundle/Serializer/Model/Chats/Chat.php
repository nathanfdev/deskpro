<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
