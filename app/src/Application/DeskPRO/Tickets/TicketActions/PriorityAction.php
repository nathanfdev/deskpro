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
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

class PriorityAction implements ActionInterface
{
	protected $priority_id;

	public function __construct($priority)
	{
		$this->priority_id = $priority;
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$ticket['priority_id'] = $this->priority_id;
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		if ($ticket['priority_id'] == $this->priority_id) {
			return array();
		}

		return array(
			array('action' => 'priority', 'priority_id' => $this->priority_id)
		);
	}


	/**
	 * Get the priority id
	 *
	 * @return int
	 */
	public function getPriorityId()
	{
		return $this->priority_id;
	}


	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		return $other_action;
	}


	/**
	 * @return string
	 */
	public function getDescription($as_html = true)
	{
		if ($this->category_id == 0) {
			return 'Remove priority';
		} else {
			$names = App::getEntityRepository('DeskPRO:TicketPriority')->getPriorityNames();
			if (!isset($names[$this->priority_id])) return '';

			return 'Set priority to ' . $names[$this->priority_id];
		}
	}
}
