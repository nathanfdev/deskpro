<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

class NewTicket extends ActionTypeAbstract
{
    /** @var \Application\DeskPRO\Entity\Ticket */
    protected $ticket;

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     */
    public function __construct(Person $person, Ticket $ticket)
    {
        $this->person = $person;
        $this->ticket = $ticket;
    }

    /**
     * Get a plain array of details that'll be stored in the databaes.
     *
     * @return array
     */
    public function getDetails()
    {
        return [
            'ticket_id' => $this->ticket['id'],
            'subject'   => $this->ticket['subject'],
        ];
    }
}
