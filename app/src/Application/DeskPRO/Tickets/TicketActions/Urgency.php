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
 * Modifies the ticket urgency
 */
class UrgencyAction implements ActionInterface
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
		$ticket['urgency'] = $ticket['urgency'] + $this->num;
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		return array(
			array('action' => 'urgency', 'urgency' => $ticket['urgency'] + $this->num)
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
		return new self($this->getNum() + $other_action->getNum());
	}

	/**
	 * @return string
	 */
	public function getDescription($as_html = true)
	{
		if (!$this->num) return '';

		if ($this->num < 0) {
			return 'Decrease urgency by ' . abs($this->num);
		} else {
			return 'Increase urgency by ' . $this->num;
		}
	}
}
