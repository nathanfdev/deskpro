<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketStringCheckTest.php';

class CheckSubjectTest extends AbstractTicketStringCheckTest
{
	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClass()
	{
		return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckSubject';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClassOptionKey()
	{
		return 'subject';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTicketPropertyName()
	{
		return 'subject';
	}
}