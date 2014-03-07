<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckDepartmentTest extends AbstractTicketEntityCheckTest
{
	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClass()
	{
		return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckDepartment';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClassOptionKey()
	{
		return 'department_ids';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass()
	{
		return 'Application\\DeskPRO\\Entity\\Department';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTicketPropertyName()
	{
		return 'department';
	}
}