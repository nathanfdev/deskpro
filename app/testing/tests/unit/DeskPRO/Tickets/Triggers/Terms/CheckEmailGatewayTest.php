<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\EmailGateway;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckEmailGatewayTest extends AbstractTicketEntityCheckTest
{
	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClass()
	{
		return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckEmailGateway';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClassOptionKey()
	{
		return 'gateway_ids';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass()
	{
		return 'Application\\DeskPRO\\Entity\\EmailGateway';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTicketPropertyName()
	{
		return 'email_gateway';
	}

	/**
	 * {@inheritDoc}
	 */
	public function createEntityObject($id)
	{
		$object = new EmailGateway('tickets');
		$object->id = $id;
		return $object;
	}
}