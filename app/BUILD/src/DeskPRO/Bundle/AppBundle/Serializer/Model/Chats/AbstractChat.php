<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\ChatConversation;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractChat.
 */
abstract class AbstractChat
{
    /**
     * The unique id of chat conversation.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * The user who started the chat.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * User chat: The users name, if they arent a person.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $personName = '';

    /**
     * User chat: The users email, if they aren`t a person.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $personEmail = '';

    /**
     * This is the agent assigned.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $agent = null;

    /**
     * Status of the chat conversation.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $status = 'open';

    /**
     * Ticket brand.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Brand>")
     *
     * @var Brand
     */
    protected $brand;

    /**
     * Department which chat was assigned.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @var \Application\DeskPRO\Entity\Department
     */
    protected $department = null;

    /**
     * Date when chat was started.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Date when agent typed last time.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateAgentTyping;

    /**
     * Date when chat was ended.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateEnded;

    /**
     * Who ended the chat.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $endedBy = '';

    /**
     * True if transcript should be send.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $shouldSendTranscript = false;

    /**
     * Date when transcript was sent.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateTranscriptSent = null;

    /**
     * Date when transcript was sent.
     *
     * @JMS\Type("boolean")
     *
     * @var \DateTime
     */
    protected $needValidateEmail = null;

    /**
     * Constructor.
     *
     * @param ChatConversation $chat
     */
    public function __construct(ChatConversation $chat)
    {
        $this->id                   = $chat->getId();
        $this->person               = $chat->getPerson();
        $this->personName           = $chat->getPersonName();
        $this->personEmail          = $chat->getPersonEmail();
        $this->agent                = $chat->getAgent();
        $this->status               = $chat->getStatus();
        $this->brand                = $chat->getBrand();
        $this->department           = $chat->getDepartment();
        $this->dateCreated          = $chat->getDateCreated();
        $this->dateAgentTyping      = $chat->getDateAgentTyping();
        $this->dateEnded            = $chat->getDateEnded();
        $this->endedBy              = $chat->getEndedBy();
        $this->shouldSendTranscript = $chat->getShouldSendTranscript();
        $this->dateTranscriptSent   = $chat->getDateTranscriptSent();
        $this->needValidateEmail    = $chat->getEmailValidationCode() && !$chat->getEmailValidated();
    }
}
