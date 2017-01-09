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
     * Constructor.
     *
     * @param ChatMessageEntity $chat
     */
    public function __construct(ChatMessageEntity $chat)
    {
        $this->id          = $chat->getId();
        $this->author      = $chat->getAuthor();
        $this->content     = $chat->getContent();
        $this->contentHtml = $chat->getContentHtml();
    }
}
