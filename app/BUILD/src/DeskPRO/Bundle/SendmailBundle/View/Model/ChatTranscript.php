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

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use JMS\Serializer\Annotation as JMS;

class ChatTranscript extends EmailBaseType
{
    /**
     * The chat conversation.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Chats\Chat")
     *
     * @var ChatConversation
     */
    protected $convo;

    /**
     * The chat messages.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Chats\ChatMessage>")
     *
     * @var ChatMessage[]
     */
    protected $convoMessages;

    protected static $templateFile = 'emails_user:chat_transcript.html.twig';

    /**
     * ChatTranscript constructor.
     *
     * @param $chat
     * @param $convoMessages
     */
    public function __construct($chat, $convoMessages)
    {
        $this->convo         = $chat;
        $this->convoMessages = $convoMessages;
    }
}
