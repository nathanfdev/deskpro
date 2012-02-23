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
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;

use Orb\Util\Numbers;

/**
 * Sets the ticket urgency to a specifc value
 */
class UrgencySetAction implements ActionInterface
{
	protected $num;

	public function __construct($num)
	{
		$this->num = $num;
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$ticket['urgency'] = $this->num;
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		if ($ticket['urgency'] == $this->num) {
			return array();
		}

		return array(
			array('action' => 'urgency', 'urgency' => $this->num)
		);
	}


	/**
	 * Get the number modifier
	 *
	 * @return int
	 */
	public function getNum()
	{
		return $this->num;
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
		return 'Set urgency to ' . $this->num;
	}
}
