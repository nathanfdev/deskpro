<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\ORM\StateChange\Ticket\ChangeSplitFrom;
use Application\DeskPRO\ORM\StateChange\Ticket\ChangeSplitTo;
use Application\DeskPRO\People\PersonContextInterface;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;

/**
 * Splits a ticket from one message and on into a new ticket.
 */
class TicketSplit implements PersonContextInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var TicketManager
     */
    private $ticket_manager;

    /**
     * @var Person
     */
    private $person_context;

    /**
     * @param Ticket $ticket
     */
    public function __construct(Ticket $ticket)
    {
        $this->em             = App::$container->getEm();
        $this->ticket_manager = App::$container->getTicketManager();
        $this->ticket         = $ticket;
    }

    /**
     * @param Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * @param string $subject
     * @param array  $message_ids
     *
     * @throws \Exception
     *
     * @return Ticket
     */
    public function split($subject, array $message_ids)
    {
        if (!$message_ids) {
            throw new \InvalidArgumentException('No messages', 100);
        }

        if (!$this->person_context) {
            throw new \InvalidArgumentException('Missing person context', 300);
        }

        $this->ticket_manager->markAsManaged($this->ticket);
        try {
            $ticket = $this->doSplit($subject, $message_ids);
            $this->ticket_manager->markAsUnmanaged($this->ticket);

            return $ticket;
        } catch (\Exception $e) {
            $this->ticket_manager->markAsUnmanaged($this->ticket);
            throw $e;
        }
    }

    /**
     * @param string $subject
     * @param array  $message_ids
     *
     * @throws \InvalidArgumentException
     *
     * @return Ticket
     */
    private function doSplit($subject, array $message_ids)
    {
        //------------------------------
        // Get and verify messages
        //------------------------------

        $messages = $this->em->createQuery('
            SELECT m
            FROM DeskPRO:TicketMessage m
            WHERE m.id IN (?0) AND m.ticket = ?1
        ')->execute([$message_ids, $this->ticket->id]);

        if (!count($messages)) {
            throw new \InvalidArgumentException('No messages', 100);
        }

        $count_all = $this->em->getConnection()->fetchColumn('
            SELECT COUNT(*)
            FROM tickets_messages
            WHERE ticket_id = ?
        ', [$this->ticket->id]);
        if ($count_all == count($messages)) {
            throw new \InvalidArgumentException('Cannot split the entire ticket', 200);
        }

        //------------------------------
        // Create new ticket copy
        //------------------------------

        $new_ticket = $this->ticket_manager->createTicket();
        $this->ticket->copyTo($new_ticket);

        $message_ids = [];

        $first      = null;
        $firstAgent = null;
        foreach ($messages as $m) {
            /* @var $m TicketMessage */
            $message_ids[] = $m->id;
            $new_ticket->addMessage($m);

            if (!$first) {
                $first = $m;
            }

            if ($m->person['is_agent'] && !$firstAgent) {
                $firstAgent = $m;
            }

            foreach ($m->attachments as $attach) {
                $attach->ticket = $new_ticket;
            }
        }

        /*
         * set dates:
         * date_feedback_rating -> copy (and make sure to copy feedback_rating as well)
         * date_created -> date_created of the first message in the ticket
         * date_first_agent_assign -> do not copy (i.e., null)
         * date_first_agent_reply -> date_created of first agent message in the ticket
         * date_resolved -> null if not resolved
         * date_archived -> null if not archived
         * date_status -> right now (i.e., new \DateTime())
         * date_agent_waiting -> if status is awaiting_user, then NOW. else, null
         * date_user_waiting -> if status is awaiting_agent, then NOW. else, null
         * total_to_first_reply should be seconds between date_created and date_first_agent_reply
         * total_user_waiting set to total_to_first_reply
         */
        $new_ticket->date_feedback_rating    = $this->ticket->date_feedback_rating;
        $new_ticket->feedback_rating         = $this->ticket->feedback_rating;
        $new_ticket->date_created            = $first->date_created;
        $new_ticket->date_first_agent_assign = null;
        $new_ticket->date_first_agent_reply  = $firstAgent ? $firstAgent->date_created : null;
        $new_ticket->date_resolved           = $this->ticket->date_resolved;
        $new_ticket->date_archived           = $this->ticket->date_archived;
        $new_ticket->date_status             = new \DateTime();
        $new_ticket->date_agent_waiting      = TicketStatus::STATUS_TYPE_AWAITING_USER === $this->ticket->status ? new \DateTime() : null;
        $new_ticket->date_user_waiting       = TicketStatus::STATUS_TYPE_AWAITING_AGENT === $this->ticket->status ? new \DateTime() : null;
        $new_ticket->total_to_first_reply    = $firstAgent ? $firstAgent->date_created->getTimestamp() - $first->date_created->getTimestamp() : 0;
        $new_ticket->total_user_waiting      = $new_ticket->total_to_first_reply ?: 0;

        $new_ticket->creation_system = Ticket::CREATED_WEB_AGENT;

        if ($subject) {
            $new_ticket->subject = $subject;
        }

        $has_owner = false;
        foreach ($new_ticket->messages as $message) {
            if ($message->person->id == $new_ticket->person->id) {
                $has_owner = true;
                break;
            }
        }

        if (count($messages) == 1 || !$has_owner) {
            $message                  = reset($messages);
            $new_ticket->person       = $message->person;
            $new_ticket->person_email = $message->person->primary_email;
            $new_ticket->organization = $message->person->organization;
        }

        //------------------------------
        // Save new ticket
        //------------------------------

        $context = $this->ticket_manager->createAgentExecutorContext(
            $this->person_context,
            'update',
            'web'
        );

        $split_from_change = new ChangeSplitFrom('split_from', $this->ticket->id, $message_ids);
        $new_ticket->getStateChangeRecorder()->recordChange($split_from_change);

        $this->ticket_manager->saveTicket($new_ticket, $context);

        //------------------------------
        // Save old ticket
        //------------------------------

        $split_to_change = new ChangeSplitTo('split_to', $new_ticket->id, $message_ids);
        $this->ticket->getStateChangeRecorder()->recordChange($split_to_change);

        $this->ticket_manager->saveTicket($this->ticket, $context);

        return $new_ticket;
    }
}
