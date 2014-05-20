<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\ExecutorContext;

require_once 'AbstractTicketStringCheckTest.php';

class CheckAgentNoteTest extends AbstractStringCheckTest
{
	/**
	 * @param int $id
	 * @param string $test_string
	 * @return Ticket
	 */
	public function createTicket($id, $test_string)
	{
		$ticket = new Ticket();
		$ticket->id = $id;

		$person = new Person();
		$person->is_agent = true;

		$message = new TicketMessage();
		$message->person = $person;
		$message->message = $test_string;
		$message->is_agent_note = true;
		$ticket->addMessage($message);

		return $ticket;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClass()
	{
		return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckAgentNote';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClassOptionKey()
	{
		return 'message';
	}

	public function testNoMessage()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$check = $this->createChecker('is', array('%OPT%' => $this->getString1()));
		$this->assertFalse($check->isTriggerMatch($ticket, $exec));
	}

	public function testNotIsset()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$check = $this->createChecker('isset', array('%OPT%' => $this->getString1()));
		$this->assertFalse($check->isTriggerMatch($ticket, $exec));

		$check = $this->createChecker('not_isset', array('%OPT%' => $this->getString1()));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));
	}
}