<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class TicketNewReminder extends EmailBaseType
{
    /**
     * A link to validate the ticket.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $verifyUrl;

    /**
     * The time the ticket form will expire.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $expireDate;

    protected $templateFile = 'emails_user:ticket_new_reminder.html.twig';

    public function __construct($verifyUrl, $expireDate)
    {
        $this->verifyUrl  = $verifyUrl;
        $this->expireDate = $expireDate;
    }
}
