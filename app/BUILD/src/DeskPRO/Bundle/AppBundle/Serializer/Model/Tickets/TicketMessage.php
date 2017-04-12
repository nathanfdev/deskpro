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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage as TicketMessageEntity;
use Application\DeskPRO\Entity\TicketMessageTranslated;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageAttribute;
use JMS\Serializer\Annotation as JMS;

class TicketMessage
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * Ticket with which this message is associated.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Ticket>")
     *
     * @var Ticket
     */
    private $ticket;

    /**
     * Person this message was sent by.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $person;

    /**
     * Info about email source, if message comes from such source.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\EmailSource>")
     *
     * @var EmailSource
     */
    protected $emailSource;

    /**
     * @JMS\Type("collection<entity<DeskPRO\Bundle\AppBundle\Entity\TicketMessageAttribute>>")
     *
     * @var TicketMessageAttribute[]
     */
    protected $attributes;

    /**
     * Items attached to the ticket.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\TicketAttachment>>")
     *
     * @var TicketAttachment[]
     */
    protected $attachments;

    /**
     * Date when message was created.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Is this message agent note?
     *
     * @JMS\Type("integer")
     *
     * @var bool
     */
    protected $isAgentNote = false;

    /**
     * How this message was created.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $creationSystem = 'web';

    /**
     * An ip address from which message was sent.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $ipAddress = '';

    /**
     * Unique ID of visitor left this message.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $visitorId;

    /**
     * Host from which message was left.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $hostname = '';

    /**
     * Country message is from.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $geoCountry = null;

    /**
     * The email address the user sent the email from (gateway messages only).
     * This is a perm record and doesnt change even if the user changes/deletes their email
     * address.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $email = '';

    /**
     * An unique hash of message.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $messageHash;

    /**
     * The primary translation is the one sent to the user.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketMessageTranslated>")
     *
     * @var TicketMessageTranslated
     */
    protected $primaryTranslation;

    /**
     * The message, will be in HTML!
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $message = '';

    /**
     * This is the full message, including all quotes/cut content.
     * This will still be the HTMLPurifier'ed content (so it's safe),
     * it's just the message before it's been run through the cutter.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $messageFull = null;

    /**
     * This is the full raw message content. It has not been passed through
     * any HTML cleaning process.s.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $messageRaw = null;

    /**
     * A hint to say if we should show message_full by default. We do this when
     * we detect that the user has replied to a message inline rather than above the cut line.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $showFullHint = false;

    /**
     * The set/detected lang code.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $langCode = null;

    public function __construct(TicketMessageEntity $ticketMessage)
    {
        $this->id                 = $ticketMessage->getId();
        $this->ticket             = $ticketMessage->getTicket();
        $this->person             = $ticketMessage->getPerson();
        $this->emailSource        = $ticketMessage->email_source;
        $this->attributes         = $ticketMessage->getAttributes();
        $this->attachments        = $ticketMessage->getAttachments();
        $this->dateCreated        = $ticketMessage->getDateCreated();
        $this->isAgentNote        = $ticketMessage->isAgentNote();
        $this->creationSystem     = $ticketMessage->creation_system;
        $this->ipAddress          = $ticketMessage->getIpAddress();
        $this->visitorId          = $ticketMessage->getVisitorId();
        $this->hostname           = $ticketMessage->getHostname();
        $this->geoCountry         = $ticketMessage->getGeoCountry();
        $this->email              = $ticketMessage->email;
        $this->messageHash        = $ticketMessage->getMessageHash();
        $this->primaryTranslation = $ticketMessage->getPrimaryTranslation();
        $this->message            = $ticketMessage->getMessageHtml();
        $this->messageFull        = $ticketMessage->getMessageFull();
        $this->messageRaw         = $ticketMessage->message_raw;
        $this->showFullHint       = $ticketMessage->show_full_hint;
        $this->langCode           = $ticketMessage->lang_code;
    }
}
