<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckLanguageTest extends AbstractTicketEntityCheckTest
{
	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClass()
	{
		return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckLanguage';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClassOptionKey()
	{
		return 'language_ids';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass()
	{
		return 'Application\\DeskPRO\\Entity\\Language';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTicketPropertyName()
	{
		return 'language';
	}
}