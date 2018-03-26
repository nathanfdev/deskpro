<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

interface PermissionableAction
{
    /**
     * True to stop processing actions after this one.
     *
     * @param Ticket $ticket
     * @param Person $person
     *
     * @return bool
     */
    public function checkPermission(Ticket $ticket, Person $person);
}
