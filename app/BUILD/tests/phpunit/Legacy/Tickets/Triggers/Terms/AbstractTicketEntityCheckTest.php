<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;

require_once 'AbstractEntityCheckTest.php';

abstract class AbstractTicketEntityCheckTest extends AbstractEntityCheckTest
{
    /**
     * The property on the ticket that is being checked.
     *
     * @return string
     */
    abstract public function getTicketPropertyName();

    /**
     * @param int    $id
     * @param object $object
     *
     * @return Ticket
     */
    public function createTicket($id, $object)
    {
        $prop_name = $this->getTicketPropertyName();

        $ticket     = new Ticket();
        $ticket->id = $id;

        if ($object !== null) {
            $ticket->$prop_name = $object;
        }

        $this->configureTicket($ticket);

        return $ticket;
    }

    /**
     * @param Ticket $ticket
     */
    protected function configureTicket(Ticket $ticket)
    {
        // nothing
    }
}
