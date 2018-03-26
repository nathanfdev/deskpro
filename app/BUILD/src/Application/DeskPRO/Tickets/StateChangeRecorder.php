<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\ORM\StateChange\StateChangeRecorder as BaseStateChangeRecorder;

class StateChangeRecorder extends BaseStateChangeRecorder
{
    /**
     * @var array
     */
    private static $trivial_fields = [
        'access_codes'            => true,
        'ticket_hash'             => true,
        'date_feedback_rating'    => true,
        'date_created'            => true,
        'date_resolved'           => true,
        'date_archived'           => true,
        'date_first_agent_assign' => true,
        'date_first_agent_reply'  => true,
        'date_last_agent_reply'   => true,
        'date_last_user_reply'    => true,
        'date_agent_waiting'      => true,
        'date_user_waiting'       => true,
        'date_status'             => true,
        'total_user_waiting'      => true,
        'total_to_first_reply'    => true,
        'locked_by_agent'         => true,
        'date_locked'             => true,
        'has_attachments'         => true,
        'count_agent_replies'     => true,
        'count_user_replies'      => true,
    ];

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    /**
     * @var bool
     */
    private $no_id = false;

    /**
     * If this is a trivial changeset.
     *
     * @var bool
     */
    private $is_trivial = false;

    /**
     * When the last trivial check was made.
     *
     * @var null
     */
    private $is_trivial_checkid = null;

    /**
     * @param Ticket $ticket
     */
    public function __construct(Ticket $ticket)
    {
        parent::__construct();

        $this->ticket = $ticket;
        if (!$ticket->id) {
            $this->no_id = true;
        }
    }

    /**
     * @return bool
     */
    public function isTrivialChangeSet()
    {
        if ($this->is_trivial_checkid === null || $this->is_trivial_checkid < $this->getStateVersion()) {
            $this->is_trivial         = true;
            $this->is_trivial_checkid = $this->getStateVersion();

            foreach ($this->getChangedFields() as $f) {
                if (!isset(self::$trivial_fields[$f])) {
                    $this->is_trivial = false;
                    break;
                }
            }
        }

        return $this->is_trivial;
    }

    /**
     * @return bool
     */
    public function isNewTicket()
    {
        // If the ticket is not a proxy object it means it was created now.
        // - If there was no ID at the time this state recorder was created,
        // it means its part of the same state transaction. (eg state recorder wasnt reset)
        if ($this->no_id && get_class($this->ticket) === 'Application\\DeskPRO\\Entity\\Ticket') {
            return true;
        }

        return false;
    }

    /**
     * Check if there has been a new reply of type.
     *
     * @param string $type
     *
     * @return bool
     */
    private function hasNewMessageOfType($type)
    {
        if (!$this->hasChangedField('message')) {
            return false;
        }

        foreach (array_reverse($this->getChangesForField('message')) as $change) {
            $message = $change->getNew();
            if (!$message) {
                continue;
            }

            switch ($type) {
                case 'agent_reply':
                    if (!$message->is_agent_note && $message->person->is_agent) {
                        return true;
                    }
                    break;
                case 'agent_note':
                    if ($message->is_agent_note) {
                        return true;
                    }
                    break;
                case 'user_reply':
                    if (!$message->is_agent_note && !$message->person->is_agent) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Get new messages of type.
     *
     * @param string $type
     *
     * @return bool
     */
    private function getNewMessagesOfType($type = 'any')
    {
        if (!$this->hasChangedField('message')) {
            return [];
        }

        $messages = [];

        foreach (array_reverse($this->getChangesForField('message')) as $change) {
            $message = $change->getNew();
            if (!$message) {
                continue;
            }

            switch ($type) {
                case 'any':
                    $messages[] = $message;
                    break;

                case 'agent_reply':
                    if (!$message->is_agent_note && $message->person->is_agent) {
                        $messages[] = $message;
                    }
                    break;
                case 'agent_note':
                    if ($message->is_agent_note) {
                        $messages[] = $message;
                    }
                    break;
                case 'user_reply':
                    if (!$message->is_agent_note && !$message->person->is_agent) {
                        $messages[] = $message;
                    }
                    break;
            }
        }

        return $messages;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->hasChangedField('status') && $this->ticket->isDeleted();
    }

    /**
     * Has there been a new agent reply?
     *
     * @return bool
     */
    public function hasNewReply()
    {
        return $this->hasChangedField('message');
    }

    /**
     * Has there been a new agent reply?
     *
     * @return bool
     */
    public function hasNewAgentReply()
    {
        return $this->hasNewMessageOfType('agent_reply');
    }

    /**
     * Has there been a new agent note?
     *
     * @return bool
     */
    public function hasNewAgentNote()
    {
        return $this->hasNewMessageOfType('agent_note');
    }

    /**
     * Has there been a new user reply?
     *
     * @return bool
     */
    public function hasNewUserReply()
    {
        return $this->hasNewMessageOfType('user_reply');
    }

    /**
     * Get an array of any new repies.
     *
     * @return \Application\DeskPRO\Entity\TicketMessage[]
     */
    public function getNewReplies()
    {
        return $this->getNewMessagesOfType('any');
    }

    /**
     * Get an array of any new agent replies.
     *
     * @return \Application\DeskPRO\Entity\TicketMessage[]
     */
    public function getNewAgentReplies()
    {
        return $this->getNewMessagesOfType('agent_reply');
    }

    /**
     * Get an array of any new agent notes.
     *
     * @return \Application\DeskPRO\Entity\TicketMessage[]
     */
    public function getNewAgentNotes()
    {
        return $this->getNewMessagesOfType('agent_note');
    }

    /**
     * Get an array of any new user replies.
     *
     * @return \Application\DeskPRO\Entity\TicketMessage[]
     */
    public function getNewUserReplies()
    {
        return $this->getNewMessagesOfType('user_reply');
    }
}
