<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

/**
 * Adds participants
 */
class AddParticipants implements ActionInterface
{
	protected $add_people_ids;

	public function __construct(array $add_participants)
	{
		$this->add_people_ids = $add_participants;
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$people = App::getEntityRepository('DeskPRO:Person')->getPeopleFromIds($this->add_people_ids);
		foreach ($people as $person) {
			$ticket->addParticipantPerson($person);
		}
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		$actions = array();

		foreach ($this->add_people_ids as $pid) {
			$actions[] = array(
				'action' => 'add_participant',
				'person_id' => $pid
			);
		}

		return $actions;
	}


	/**
	 * Get the agent id
	 *
	 * @return int
	 */
	public function getPersonIds()
	{
		return $this->add_people_ids;
	}


	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		$ids = $this->getPersonIds();
		$ids = array_merge($other_action->getPersonIds());
		$ids = array_unique($ids);

		return $ids;
	}
}
