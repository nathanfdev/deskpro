<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckWorkflowTest extends AbstractTicketEntityCheckTest
{
	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClass()
	{
		return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckWorkflow';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClassOptionKey()
	{
		return 'workflow_ids';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass()
	{
		return 'Application\\DeskPRO\\Entity\\TicketWorkflow';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTicketPropertyName()
	{
		return 'workflow';
	}
}