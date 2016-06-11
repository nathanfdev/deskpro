<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use JMS\Serializer\Annotation as JMS;

class Chat
{
    /**
     * The unique id of chat conversation.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * Subject of the chat conversation.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $subject;

    /**
     * If this is a user conversation, this is the user who started the chat.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     * @JMS\Expose()
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * User chat: The users name, if they arent a person.
     *
     * @JMS\Type("string")
     * @JMS\Expose()
     *
     * @var string
     */
    protected $person_name = '';

    /**
     * User chat: The users email, if they aren`t a person.
     *
     * @JMS\Type("string")
     * @JMS\Expose()
     *
     * @var string
     */
    protected $person_email = '';

    /**
     * If this is a user conversation, this is the agent assigned.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     * @JMS\Expose()
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $agent = null;

    /**
     * Status of the chat conversation.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $status = 'open';

    /**
     * Department which chat was assigned.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     * @JMS\Expose()
     *
     * @var \Application\DeskPRO\Entity\Department
     */
    protected $department = null;

    /**
     * Date when chat was started.
     *
     * @JMS\Type("DateTime")
     * @JMS\Expose()
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Date when agent typed last time.
     *
     * @JMS\Type("DateTime")
     * @JMS\Expose()
     *
     * @var \DateTime
     */
    protected $date_agent_typing;

    /**
     * Date when chat was ended.
     *
     * @JMS\Type("DateTime")
     * @JMS\Expose()
     *
     * @var \DateTime
     */
    protected $date_ended;

    /**
     * Who ended the chat.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $ended_by = '';

    /**
     * True if transcript should be send.
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $should_send_transcript = false;

    /**
     * Date when transcript was sent.
     *
     * @JMS\Type("DateTime")
     * @JMS\Expose()
     *
     * @var \DateTime
     */
    protected $date_transcript_sent = null;

    /**
     * Date when transcript was sent.
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var \DateTime
     */
    protected $need_validate_email = null;

    public function __construct(ChatConversation $chat)
    {
        $this->id                     = $chat->getId();
        $this->subject                = $chat->getSubjectLine();
        $this->department             = $chat->getDepartment();
        $this->person                 = $chat->getPerson();
        $this->agent                  = $chat->getAgent();
        $this->person_name            = $chat->getPersonName();
        $this->person_email           = $chat->getPersonEmail();
        $this->date_created           = $chat->getDateCreated();
        $this->date_agent_typing      = $chat->getDateAgentTyping();
        $this->date_ended             = $chat->getDateEnded();
        $this->ended_by               = $chat->getEndedBy();
        $this->should_send_transcript = $chat->getShouldSendTranscript();
        $this->date_transcript_sent   = $chat->getDateTranscriptSent();
        $this->need_validate_email    = $chat->getNeedValidateEmail();
    }
}
