<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use Application\DeskPRO\Entity\TicketFeedback;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketMessage;
use JMS\Serializer\Annotation as JMS;

abstract class TicketEmailType extends EmailBaseType
{
    /**
     * The ticket.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket")
     *
     * @var Ticket
     */
    protected $ticket;

    /**
     * The person who opened the ticket.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $ticketPerson;

    /**
     * The agent assigned to the ticket.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $ticketAgent;

    /**
     * Ticket messages.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketMessage>")
     *
     * @var TicketMessage[]
     */
    protected $ticketMessages;

    /**
     * A link to the ticket.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $ticketLink;

    /**
     * Ticket satisfactions.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketFeedback>")
     *
     * @var TicketFeedback[]
     */
    protected $ticketSatisfaction;

    /**
     * TicketEmailType constructor.
     *
     * @param Ticket           $ticket
     * @param Person           $ticketPerson
     * @param Person           $ticketAgent
     * @param string           $ticketLink
     * @param TicketMessage[]  $ticketMessages
     * @param TicketFeedback[] $ticketSatisfaction
     */
    public function __construct(Ticket $ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages, $ticketSatisfaction)
    {
        $this->ticket = $ticket;

        $this->ticketPerson = $ticketPerson;

        $this->ticketLink = $ticketLink;

        $this->ticketAgent = $ticketAgent;

        $this->ticketMessages = $ticketMessages;

        $this->ticketSatisfaction = $ticketSatisfaction;
    }
}
