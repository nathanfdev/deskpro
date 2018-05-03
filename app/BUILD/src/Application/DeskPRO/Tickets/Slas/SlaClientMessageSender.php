<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\Slas;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketSla;
use Application\DeskPRO\People\PersonContextInterface;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketUpdatedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SlaClientMessageSender implements PersonContextInterface
{
    const CHANNEL = 'agent.ticket-sla-updated';

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var array()
     */
    private $queue = [];

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param Connection               $db
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Connection $db, EventDispatcherInterface $eventDispatcher)
    {
        $this->db              = $db;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person = $person;
    }

    /**
     * @param Ticket    $ticket         The ticket
     * @param TicketSla $ticket_sla     The SLA on the ticket
     * @param string    $orig_status    The original status before it was updated
     * @param string    $orig_completed The original is_completed state before it was updated
     */
    public function sendMessage(Ticket $ticket, TicketSla $ticket_sla, $orig_status, $orig_completed)
    {
        $this->eventDispatcher->dispatch(TicketUpdatedEvent::EVENT_NAME, new TicketUpdatedEvent(
            self::CHANNEL,
            [
                'ticket_id'             => $ticket->getId(),
                'ticket_agent_id'       => $ticket->agent ? $ticket->agent->id : null,
                'ticket_Agent_team_id'  => $ticket->agent_team ? $ticket->agent_team->id : null,
                'sla_id'                => $ticket_sla->sla->id,
                'sla_status'            => $ticket_sla->sla_status,
                'original_status'       => $orig_status,
                'warn_date'             => $ticket_sla->warn_date ? $ticket_sla->warn_date->format('c') : null,
                'fail_date'             => $ticket_sla->fail_date ? $ticket_sla->fail_date->format('c') : null,
                'is_completed'          => $ticket_sla->is_completed,
                'original_is_completed' => $orig_completed,
                'removed'               => $ticket->hasSla($ticket_sla->sla) ? false : true,
                'via_person'            => $this->person ? $this->person->id : null,
            ]
        ));
    }
}
