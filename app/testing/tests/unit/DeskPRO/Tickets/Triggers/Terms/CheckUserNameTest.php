<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

require_once 'AbstractStringCheckTest.php';

class CheckUserNameTest extends AbstractStringCheckTest
{
	/**
	 * @param int $id
	 * @param string $test_string
	 * @return Ticket
	 */
	public function createTicket($id, $test_string)
	{
		$person = new Person();
		$person->id = $id;
		$person->name = $test_string;

		$ticket = new Ticket();
		$ticket->id = $id;
		$ticket->person = $person;

		return $ticket;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClass()
	{
		return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckUserName';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClassOptionKey()
	{
		return 'name';
	}
}