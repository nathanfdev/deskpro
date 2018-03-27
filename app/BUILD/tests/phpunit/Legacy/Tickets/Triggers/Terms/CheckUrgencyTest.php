<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckUrgency;
use DpTest\DeskProTestCase;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckUrgencyTest extends DeskProTestCase
{
    public function testIntMatch()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $ticket->status  = 'awaiting_agent';
        $ticket->urgency = 5;

        $check = new CheckUrgency('gt', ['urgency1' => 4]);
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckUrgency('gt', ['urgency1' => 6]);
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));

        $check = new CheckUrgency('lt', ['urgency1' => 6]);
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }

    public function testIntRangeMatch()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $ticket->status  = 'awaiting_agent';
        $ticket->urgency = 5;

        $check = new CheckUrgency('between', ['urgency1' => 4, 'urgency2' => 10]);
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckUrgency('between', ['urgency1' => 5, 'urgency2' => 10]);
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckUrgency('notbetween', ['urgency1' => 8, 'urgency2' => 10]);
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));
    }
}
