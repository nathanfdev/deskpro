<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketMessage;
use JMS\Serializer\Annotation as JMS;

class AgentTicketEmailType extends TicketEmailType
{
    /**
     * Participants of the ticket.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person>")
     *
     * @var array
     */
    protected $participants;

    /**
     * Information about ticket layout.
     *
     * @var array
     */
    protected $ticketLayout;

    protected $customFields;

    protected $customUserFields;

    /**
     * TicketEmailType constructor.
     *
     * @param Ticket        $ticket
     * @param Person        $ticketPerson
     * @param Person        $ticketAgent
     * @param string        $ticketLink
     * @param TicketMessage $ticketMessages
     * @param array         $participants
     * @param               $ticketLayout
     * @param $customFields
     * @param $customUserFields
     */
    public function __construct(
        Ticket $ticket,
        $ticketPerson,
        $ticketAgent,
        $ticketLink,
        $ticketMessages,
        $participants,
        $ticketLayout,
        $customFields,
        $customUserFields
    ) {
        parent::__construct($ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages);

        $this->participants     = $participants;
        $this->ticketLayout     = $ticketLayout;
        $this->customFields     = $customFields;
        $this->customUserFields = $customUserFields;
    }
}
