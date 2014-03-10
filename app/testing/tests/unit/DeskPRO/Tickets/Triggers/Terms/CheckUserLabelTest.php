<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\LabelPerson;

require_once 'AbstractEntityCheckTest.php';

class CheckUserLabelTest extends AbstractEntityCheckTest
{
	/**
	 * @param int $id
	 * @param $object
	 * @return Ticket
	 */
	public function createTicket($id, $object)
	{
		$person = new Person();

		$bogus = new LabelPerson();
		$bogus->label = "bogus";

		$person->labels->add($bogus);
		$person->labels->add($object);

		$ticket = new Ticket();
		$ticket->id = $id;
		$ticket->person = $person;

		return $ticket;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getCheckClass()
	{
		return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckUserLabel';
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
		return 'Application\DeskPRO\Entity\LabelPerson';
	}

	/**
	 * @param int $id
	 * @return object
	 */
	public function createEntityObject($id)
	{
		$object = new LabelPerson();
		$object->label = $id;

		return $object;
	}
}