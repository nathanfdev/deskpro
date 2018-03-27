<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Escalations;

use Application\DeskPRO\Entity\TicketEscalation;

class EscalationTicketMatcherTest extends EscalationTicketMatcher
{
    private $matches;

    /**
     * @param \Application\DeskPRO\Entity\Ticket[] $tickets
     */
    public function setTickets(array $tickets)
    {
        $this->matches = $tickets;
    }

    /**
     * @param TicketEscalation $esc
     * @param int              $limit
     *
     * @return \Application\DeskPRO\Entity\Ticket[]
     */
    public function getMatches(TicketEscalation $esc, $limit = 100)
    {
        return $this->matches;
    }
}
