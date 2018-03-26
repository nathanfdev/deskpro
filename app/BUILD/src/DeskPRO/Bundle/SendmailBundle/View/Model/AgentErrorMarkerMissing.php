<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use JMS\Serializer\Annotation as JMS;

class AgentErrorMarkerMissing extends TicketEmailType
{
    /**
     * Subject of the email received.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $subject;

    protected $templateFile = 'emails_agent:error_marker_missing.html.twig';

    public function __construct(
        Ticket $ticket,
        $ticketPerson,
        $ticketAgent,
        $ticketLink,
        $ticketMessages,
        $subject
    ) {
        parent::__construct($ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages);

        $this->subject = $subject;
    }
}
