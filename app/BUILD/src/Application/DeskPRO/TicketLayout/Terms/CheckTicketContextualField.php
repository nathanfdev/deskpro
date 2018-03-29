<?php

namespace Application\DeskPRO\TicketLayout\Terms;

use Application\DeskPRO\Entity\Ticket;

class CheckTicketContextualField extends \Application\DeskPRO\Tickets\Triggers\Terms\CheckTicketContextualField implements TicketLayoutTermInterface
{
    /**
     * {@inheritdoc}
     */
    public function compileJsCheck()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isTicketMatch(Ticket $ticket)
    {
    }
}
