<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckAgent;
use Orb\Scraper\Highrise\Person;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckAgentTest extends AbstractTicketEntityCheckTest
{
	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClass()
	{
		return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckAgent';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClassOptionKey()
	{
		return 'agent_ids';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass()
	{
		return 'Application\\DeskPRO\\Entity\\Person';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTicketPropertyName()
	{
		return 'agent';
	}

	public function testTouched()
	{
		$agent = new Person();

		$ticket = new Ticket();
		$ticket->agent = $agent;

		$context = new ExecutorContext();

		$this->assertTrue($ticket->getStateChangeRecorder()->hasTouchedField('agent'));

		$check = new CheckAgent('touched');
		$this->assertTrue($check->isTriggerMatch($ticket, $context));
	}

	public function testNotTouched()
	{
		$agent = new Person();

		$ticket = new Ticket();
		$ticket->agent = $agent;
		$ticket->resetStateChangeRecorder();

		$context = new ExecutorContext();

		$this->assertFalse($ticket->getStateChangeRecorder()->hasTouchedField('agent'));

		$check = new CheckAgent('nottouched');
		$this->assertTrue($check->isTriggerMatch($ticket, $context));
	}
}