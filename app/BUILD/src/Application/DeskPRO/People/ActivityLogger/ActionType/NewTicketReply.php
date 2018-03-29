<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketMessage;

class NewTicketReply extends ActionTypeAbstract
{
    /** @var \Application\DeskPRO\Entity\TicketMessage */
    protected $ticket_message;

    /**
     * @param \Application\DeskPRO\Entity\Person        $person
     * @param \Application\DeskPRO\Entity\TicketMessage $ticket_message
     */
    public function __construct(Person $person, TicketMessage $ticket_message)
    {
        $this->person         = $person;
        $this->ticket_message = $ticket_message;
    }

    /**
     * Get a plain array of details that'll be stored in the databaes.
     *
     * @return array
     */
    public function getDetails()
    {
        return [
            'ticket_id'  => $this->ticket_message->ticket['id'],
            'message_id' => $this->ticket_message['id'],
            'subject'    => $this->ticket_message->ticket['subject'],
            'message'    => $this->ticket_message->getMessageText(),
        ];
    }
}
