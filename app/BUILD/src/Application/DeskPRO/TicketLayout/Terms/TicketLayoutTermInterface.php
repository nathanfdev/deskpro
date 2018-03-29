<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout\Terms;

use Application\DeskPRO\Criteria\CriteriaTermInterface;
use Application\DeskPRO\Entity\Ticket;

interface TicketLayoutTermInterface extends CriteriaTermInterface
{
    /**
     * Should return a JS function that accepts a ticket object and returns true/false
     * depending on if the term passes/fails.
     *
     * @return string
     */
    public function compileJsCheck();

    /**
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function isTicketMatch(Ticket $ticket);
}
