<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use JMS\Serializer\Annotation as JMS;

class TicketAwaitingWarnFinal extends TicketEmailType
{
    /**
     * A link to resolve the ticket.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $ticketResolveLink;

    protected $templateFile = 'emails_user:ticket_awaiting_warn_final.html.twig';

    public function __construct(
        Ticket $ticket,
        $ticketPerson,
        $ticketAgent,
        $ticketLink,
        $ticketMessages,
        $ticketSatisfaction,
        $ticketResolveLink
    ) {
        parent::__construct($ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages, $ticketSatisfaction);

        $this->ticketResolveLink = $ticketResolveLink;
    }
}
