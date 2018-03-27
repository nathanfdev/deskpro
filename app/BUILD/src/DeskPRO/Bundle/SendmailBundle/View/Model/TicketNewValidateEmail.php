<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use JMS\Serializer\Annotation as JMS;

class TicketNewValidateEmail extends TicketEmailType
{
    /**
     * Ticket access code.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $verifyUrl;

    protected $templateFile = 'emails_user:ticket_new_validate.html.twig';

    public function __construct(Ticket $ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages, $verifyUrl)
    {
        parent::__construct($ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages);

        $this->verifyUrl = $verifyUrl;
    }
}
