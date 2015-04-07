<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckStatus;
use DpTest\DeskProTestCase;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckStatusTest extends DeskProTestCase
{
    public function testStatus()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $ticket->status = 'awaiting_agent';

        $check = new CheckStatus('is', array('status' => 'awaiting_agent'));
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckStatus('is', array('status' => 'resolved'));
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testStatusCode()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $ticket->status = 'hidden.deleted';

        $check = new CheckStatus('is', array('status' => 'hidden.deleted'));
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckStatus('is', array('status' => 'hidden.spam'));
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }
}
