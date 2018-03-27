<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker;

use Application\DeskPRO\Entity\Ticket;

/**
 * All compiled ticket checkers (returned from the engine) implement this interface.
 */
interface PhpTicketCheckerInterface
{
    /**
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function isTicketMatch(Ticket $ticket);
}
