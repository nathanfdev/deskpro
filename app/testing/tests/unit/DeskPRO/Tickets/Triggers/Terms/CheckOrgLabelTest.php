<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\LabelOrganization;

require_once 'AbstractEntityCheckTest.php';

class CheckOrgLabelTest extends AbstractEntityCheckTest
{
	/**
	 * @param int $id
	 * @param $object
	 * @return Ticket
	 */
	public function createTicket($id, $object)
	{
		$org = new Organization();

		$bogus = new LabelOrganization();
		$bogus->label = "bogus";

		$org->labels->add($bogus);
		$org->labels->add($object);

		$ticket = new Ticket();
		$ticket->id = $id;
		$ticket->organization = $org;

		return $ticket;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClass()
	{
		return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckOrgLabel';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClassOptionKey()
	{
		return 'labels';
	}

	/**
	 * The entity class we are checking
	 * @return string
	 */
	public function getEntityClass()
	{
		return 'Application\DeskPRO\Entity\LabelOrganization';
	}

	/**
	 * @param int $id
	 * @return object
	 */
	public function createEntityObject($id)
	{
		$object = new LabelOrganization();
		$object->label = $id;

		return $object;
	}
}