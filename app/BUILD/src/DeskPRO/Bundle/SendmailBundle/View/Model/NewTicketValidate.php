<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use JMS\Serializer\Annotation as JMS;

class NewTicketValidate extends TicketEmailType
{
    /**
     * Ticket access code.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $accessCode;

    protected $templateFile = 'emails_user:new_ticket_validate.html.twig';

    public function __construct(
        Ticket $ticket,
        $ticketPerson,
        $ticketAgent,
        $ticketLink,
        $ticketMessages,
        $ticketSatisfaction,
        $accessCode
    ) {
        parent::__construct($ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages, $ticketSatisfaction);

        $this->accessCode = $accessCode;
    }
}
