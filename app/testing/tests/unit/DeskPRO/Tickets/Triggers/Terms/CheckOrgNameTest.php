<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Ticket;

require_once 'AbstractStringCheckTest.php';

class CheckOrgNameTest extends AbstractStringCheckTest
{
	/*
	 * This test uses 'subject' on the ticket so AbstractStringCheckTest
	 * does most of the work.
	 *
	 * We copy the subject value to the org name that is what CheckOrgName
	 * actually tests.
	 */


	/**
	 * @param Ticket $ticket
	 * @return void
	 */
	protected function configureTicket(Ticket $ticket)
	{
		$org = new Organization();
		$org->name = $ticket->subject;
		$ticket->organization = $org;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClass()
	{
		return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckOrgName';
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