<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

require_once 'AbstractStringCheckTest.php';

class CheckUserNameTest extends AbstractStringCheckTest
{
	/*
	 * This test uses 'subject' on the ticket so AbstractStringCheckTest
	 * does most of the work.
	 *
	 * We copy the subject value to the person name that is what CheckUserName
	 * actually tests.
	 */


	/**
	 * @param Ticket $ticket
	 * @return void
	 */
	protected function configureTicket(Ticket $ticket)
	{
		$person = new Person();
		$person->name = $ticket->subject;
		$ticket->person = $person;
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

	/**
	 * {@inheritDoc}
	 */
	public function getTicketPropertyName()
	{
		return 'subject';
	}
}