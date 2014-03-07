<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckCategoryTest extends AbstractTicketEntityCheckTest
{
	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClass()
	{
		return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckCategory';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClassOptionKey()
	{
		return 'category_ids';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass()
	{
		return 'Application\\DeskPRO\\Entity\\TicketCategory';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTicketPropertyName()
	{
		return 'category';
	}
}