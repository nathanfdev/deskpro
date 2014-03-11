<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Tickets\Actions\SetUrgency;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

class SetUrgencyTest extends \DpUnitTestCase
{
	public function testSet()
	{
		$ticket = new Ticket();
		$ticket->urgency = 5;
		$exec   = new ExecutorContext();

		$action = new SetUrgency(array('mode' => 'set', 'urgency' => 10));

		$action->applyAction($ticket, $exec);

		$this->assertEquals(10, $ticket->urgency);
	}

	public function testRaise()
	{
		$ticket = new Ticket();
		$ticket->urgency = 5;
		$exec   = new ExecutorContext();

		$action = new SetUrgency(array('mode' => 'raise', 'urgency' => 8));

		$action->applyAction($ticket, $exec);

		$this->assertEquals(8, $ticket->urgency);
	}

	public function testRaiseNoop()
	{
		$ticket = new Ticket();
		$ticket->urgency = 5;
		$exec   = new ExecutorContext();

		$action = new SetUrgency(array('mode' => 'raise', 'urgency' => 4));

		$action->applyAction($ticket, $exec);

		$this->assertEquals(5, $ticket->urgency);
	}

	public function testLower()
	{
		$ticket = new Ticket();
		$ticket->urgency = 5;
		$exec   = new ExecutorContext();

		$action = new SetUrgency(array('mode' => 'lower', 'urgency' => 2));

		$action->applyAction($ticket, $exec);

		$this->assertEquals(2, $ticket->urgency);
	}

	public function testLowerNoop()
	{
		$ticket = new Ticket();
		$ticket->urgency = 5;
		$exec   = new ExecutorContext();

		$action = new SetUrgency(array('mode' => 'lower', 'urgency' => 6));

		$action->applyAction($ticket, $exec);

		$this->assertEquals(5, $ticket->urgency);
	}

	public function testAdd()
	{
		$ticket = new Ticket();
		$ticket->urgency = 5;
		$exec   = new ExecutorContext();

		$action = new SetUrgency(array('mode' => 'add', 'urgency' => 2));

		$action->applyAction($ticket, $exec);

		$this->assertEquals(7, $ticket->urgency);
	}

	public function testSub()
	{
		$ticket = new Ticket();
		$ticket->urgency = 5;
		$exec   = new ExecutorContext();

		$action = new SetUrgency(array('mode' => 'sub', 'urgency' => 2));

		$action->applyAction($ticket, $exec);

		$this->assertEquals(3, $ticket->urgency);
	}
}