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
 * Basic action for properties
 */
abstract class AbstractPropertyAction implements ActionInterface
{
	protected $value;

	public function __construct($value = 0)
	{
		$this->value = $value;
	}
	

	/**
	 * Get the property name on the ticket object
	 * 
	 * @return string
	 */
	abstract public function getPropertyName();

	
	/**
	 * Get the property value
	 *
	 * @return mixed
	 */
	public function getPropertyValue()
	{
		return $this->value;
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$prop = $this->getPropertyName();
		$val = $this->getPropertyValue();

		$ticket[$prop] = $val;
	}

	
	/**
	 * With properties, the other action always overwrites the previous action.
	 * 
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		return $other_action;
	}
}