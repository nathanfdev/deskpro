<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;

require_once 'AbstractStringCheckTest.php';

abstract class AbstractTicketStringCheckTest extends AbstractStringCheckTest
{
    /**
     * The property on the ticket that is being checked
     * @return string
     */
    abstract public function getTicketPropertyName();

    /**
     * @param $id
     * @param $test_string
     * @return Ticket
     */
    public function createTicket($id, $test_string)
    {
        $prop_name = $this->getTicketPropertyName();

        $ticket = new Ticket();
        $ticket->id = $id;
        $ticket->$prop_name = $test_string;
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
