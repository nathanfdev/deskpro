<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\People\PersonContextInterface;
use Orb\Util\Arrays;

class TicketDisplay implements PersonContextInterface
{
    /** @var \Application\DeskPRO\Entity\Ticket */
    protected $ticket;

    /** @var PersonContextInterface */
    protected $person_context;
    /** @var string */
    protected $person_type = 'user';

    /** @var array|null */
    protected $user_participants;
    /** @var array|null */
    protected $agent_participants;

    /** @var array */
    protected $notes;

    /** @var int */
    protected $message_count;

    /** @var \Application\DeskPRO\Entity\TicketMessage */
    protected $first_message;

    /** @var array */
    protected $messages;
    /** @var array */
    protected $attachments;
    /** @var array */
    protected $message_to_attach;
    /** @var array */
    protected $user_ratings;
    /** @var array */
    protected $ignore_attachments = [];

    public function __construct(Ticket $ticket, Person $person)
    {
        $this->ticket = $ticket;
        $this->setPersonContext($person);
    }

    public function setIgnoreAttachment(TicketAttachment $a)
    {
        $this->ignore_attachments[$a->id] = $a;
    }

    public function setPersonContext(Person $person, $set_type = null)
    {
        $this->person_context = $person;
        if ($person['is_agent']) {
            $this->person_type = 'agent';
        }

        if ($set_type) {
            $this->person_type = $set_type;
        }
    }

    public function getUserParticipants()
    {
        if ($this->user_participants !== null) {
            return $this->user_participants;
        }

        $this->user_participants = [];

        foreach ($this->ticket->getParticipants() as $part) {
            if (!$part->person['is_agent']) {
                $this->user_participants[] = $part;
            }
        }

        return $this->user_participants;
    }

    public function getAgentParticipants()
    {
        if ($this->agent_participants !== null) {
            return $this->agent_participants;
        }

        $this->agent_participants = [];

        foreach ($this->ticket->getParticipants() as $part) {
            if ($part->person['is_agent']) {
                $this->agent_participants[] = $part;
            }
        }

        return $this->agent_participants;
    }

    public function getNotes()
    {
        if ($this->notes !== null) {
            return $this->notes;
        }

        $this->getMessages();

        $this->notes = [];

        foreach ($this->messages as $message) {
            if ($message['is_agent_note']) {
                $this->notes[] = $message;
            }
        }

        return $this->notes;
    }

    public function getMessages($limit = 15)
    {
        if ($this->messages !== null) {
            return $this->messages;
        }

        if ($this->person_type == 'agent') {
            $this->messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages(
                $this->ticket,
                ['with_notes' => true, 'limit' => $limit, 'order' => 'DESC']
            );
        } else {
            $this->messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages(
                $this->ticket,
                ['with_notes' => false, 'limit' => $limit, 'order' => 'DESC']
            );
        }

        return $this->messages;
    }

    public function getMessageCount()
    {
        if ($this->message_count !== null) {
            return $this->message_count;
        }

        $this->message_count = (int) App::getDb()->fetchColumn('SELECT COUNT(*) FROM tickets_messages WHERE ticket_id = ?', [$this->ticket->id]);

        return $this->message_count;
    }

    public function getFirstMessage()
    {
        if ($this->first_message !== null) {
            return $this->first_message ?: null;
        }

        if ($this->messages && count($this->messages) == $this->message_count) {
            $this->first_message = Arrays::getLastItem($this->messages);
        } else {
            $this->first_message = App::getOrm()->createQuery('
                SELECT m
                FROM TicketMessage m
                WHERE m.ticket = ?0
                ORDER BY m.id DESC
            ')->setMaxResults(1)->setParameters([$this->ticket])->getOneOrNullResult();
        }

        if (!$this->first_message) {
            $this->first_message = false;
        }

        return $this->first_message;
    }

    /**
     * @return TicketAttachment[]
     */
    public function getAttachments()
    {
        if ($this->attachments !== null) {
            return $this->attachments;
        }

        $this->attachments = App::getEntityRepository('DeskPRO:TicketAttachment')->getTicketAttachments($this->ticket);

        return $this->attachments;
    }

    public function getMessagesToAttachments($include_inline = false)
    {
        if ($this->message_to_attach !== null) {
            return $this->message_to_attach;
        }

        $this->getMessages();
        $this->getAttachments();

        $this->message_to_attach = [];

        foreach ($this->attachments as $attach) {
            if (!$include_inline && $attach->is_inline) {
                continue;
            }

            if (!isset($this->message_to_attach[$attach['message']['id']])) {
                $this->message_to_attach[$attach['message']['id']] = [];
            }

            $this->message_to_attach[$attach['message']['id']][] = $attach['id'];
        }

        return $this->message_to_attach;
    }

    public function getMessageAttachments($message, $include_inline = false)
    {
        if (!$message) {
            return;
        }
        $id              = $message->getId();
        $messagetoattach = $this->getMessagesToAttachments($include_inline);

        if (!isset($messagetoattach[$id])) {
            return;
        }

        $ret = [];
        foreach ($messagetoattach[$id] as $aid) {
            if (!isset($this->ignore_attachments[$aid])) {
                $ret[$aid] = $this->attachments[$aid];
            }
        }

        return $ret;
    }

    public function getFeedbackRatings()
    {
        if ($this->user_ratings !== null) {
            return $this->user_ratings;
        }

        $this->user_ratings = App::getDb()->fetchAllKeyValue('
            SELECT message_id, rating
            FROM ticket_feedback
            WHERE ticket_id = ? AND person_id = ?
        ', [$this->ticket->getId(), $this->person_context->getId()]);

        return $this->user_ratings;
    }

    public function getDisplayArray()
    {
        $last_user_message  = 0;
        $last_agent_message = 0;

        foreach ($this->getMessages() as $message) {
            if ($message->person && $message->person->is_agent) {
                $last_agent_message = $message->id;
            } else {
                $last_user_message = $message->id;
            }
        }

        //------------------------------
        // Custom fields
        //------------------------------

        $field_manager = App::getSystemService('ticket_fields_manager');
        $custom_fields = $field_manager->getDisplayArrayForObject($this->ticket);

        return [
            'ticket' => $this->ticket,

            'user_participants'  => $this->getUserParticipants(),
            'agent_participants' => $this->getAgentParticipants(),

            'notes'             => $this->getNotes(),
            'messages'          => $this->getMessages(),
            'attachments'       => $this->getAttachments(),
            'message_to_attach' => $this->getMessagesToAttachments(),

            'last_user_message_id'  => $last_user_message,
            'last_agent_message_id' => $last_agent_message,

            'user_ratings' => $this->getFeedbackRatings(),

            'custom_fields' => $custom_fields,
        ];
    }
}
