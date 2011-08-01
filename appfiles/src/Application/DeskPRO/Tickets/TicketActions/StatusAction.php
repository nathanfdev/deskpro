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

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\Entity\Ticket;

/**
 * Sets status
 */
class StatusAction implements ActionInterface
{
	protected $status;

	public function __construct($status)
	{
		$this->status = $status;
	}

	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		if (strpos($this->status, '.') !== false) {
			list ($status, $hidden_status) = explode('.', $this->status, 2);
		} else {
			$status = $this->status;
			$hidden_status = null;
		}

		if ($hidden_status) {
			$ticket->setHiddenStatus($hidden_status);
		} else {
			$ticket->setStatus($status);
		}
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		if ($ticket->getStatusCode() == $this->status) {
			return array();
		}

		return array(
			array('action' => 'status', 'status' => $this->status)
		);
	}


	/**
	 * Get the full status (stauts.hidden_status)
	 *
	 * @return string
	 */
	public function getFullStatus()
	{
		return $this->status;
	}


	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		return $other_action;
	}
}