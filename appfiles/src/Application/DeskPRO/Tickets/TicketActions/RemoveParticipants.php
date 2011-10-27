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
 * Remove participants
 */
class RemoveParticipants implements ActionInterface
{
	protected $remove_people_ids;

	public function __construct(array $remove_participants)
	{
		$this->remove_people_ids = $remove_participants;
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$people = App::getEntityRepository('DeskPRO:Person')->getPeopleFromIds($this->remove_people_ids);
		foreach ($people as $person) {
			$ticket->removeParticipantPerson($person);
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

		foreach ($this->remove_people_ids as $pid) {
			$actions[] = array(
				'action' => 'remove_participant',
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
		return $this->remove_people_ids;
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
