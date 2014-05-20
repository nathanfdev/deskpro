<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckUrgency;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckUrgencyTest extends \DpUnitTestCase
{
	public function testIntMatch()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$ticket->status = 'awaiting_agent';
		$ticket->urgency = 5;

		$check = new CheckUrgency('gt', array('urgency1' => 4));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));

		$check = new CheckUrgency('gt', array('urgency1' => 6));
		$this->assertFalse($check->isTriggerMatch($ticket, $exec));

		$check = new CheckUrgency('lt', array('urgency1' => 6));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));
	}

	public function testIntRangeMatch()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$ticket->status = 'awaiting_agent';
		$ticket->urgency = 5;

		$check = new CheckUrgency('between', array('urgency1' => 4, 'urgency2' => 10));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));

		$check = new CheckUrgency('between', array('urgency1' => 5, 'urgency2' => 10));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));

		$check = new CheckUrgency('notbetween', array('urgency1' => 8, 'urgency2' => 10));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));
	}
}