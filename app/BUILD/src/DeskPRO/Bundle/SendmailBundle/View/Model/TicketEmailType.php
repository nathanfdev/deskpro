<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\BasePerson;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketFeedback;
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
     * The person for whom opened the ticket.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var BasePerson
     */
    protected $ticketPerson;

    /**
     * The agent assigned to the ticket.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var BasePerson
     */
    protected $ticketAgent;

    /**
     * Messages sent within the ticket.
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
     * The person who made the action with ticket.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $actionPerformer;

    /**
     * TicketEmailType constructor.
     *
     * @param Ticket           $ticket
     * @param BasePerson       $ticketPerson
     * @param BasePerson       $ticketAgent
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

    /**
     * @param BasePerson $performer
     *
     * @return $this
     */
    public function setActionPerformer(BasePerson $performer)
    {
        $this->actionPerformer = $performer;

        return $this;
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }
}
