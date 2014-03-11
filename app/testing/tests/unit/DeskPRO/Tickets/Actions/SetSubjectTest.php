<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Tickets\Actions\SetSubject;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

class SetSubjectTest extends \DpUnitTestCase
{
	public function testSet()
	{
		$ticket = new Ticket();
		$exec   = new ExecutorContext();

		$action = new SetSubject(array('subject' => 'New Subject'));

		$action->applyAction($ticket, $exec);

		$this->assertEquals('New Subject', $ticket->subject);
	}

	public function testNoop()
	{
		$ticket = new Ticket();
		$ticket->subject = "Test Subject";

		$exec = new ExecutorContext();

		$action = new SetSubject(array('subject' => 'Test Subject'));

		$this->assertTrue($action->isNoop($ticket, $exec));
	}

	public function testNoop2()
	{
		$ticket = new Ticket();
		$ticket->subject = "Test Subject";

		$exec = new ExecutorContext();

		$action = new SetSubject(array('subject' => ''));

		$this->assertTrue($action->isNoop($ticket, $exec));
	}

	public function testInvalid()
	{
		$ticket = new Ticket();
		$ticket->subject = "Test Subject";
		$exec = new ExecutorContext();

		$action = new SetSubject(array('subject' => null));
		$action->applyAction($ticket, $exec);

		$this->assertEquals("Test Subject", $ticket->subject);
	}
}