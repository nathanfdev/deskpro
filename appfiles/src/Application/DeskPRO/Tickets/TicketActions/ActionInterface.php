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

use Application\DeskPRO\Entity\Ticket;

interface ActionInterface
{
	/**
	 * Apply the action to the ticket
	 * 
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return void
	 */
	public function apply(Ticket $ticket);

	
	/**
	 * Merge this action into another, and return the new merged action.
	 *
	 * For example, if a property is set, then the "other" action would overwrite the
	 * "this" action, so you could just return "other"
	 *
	 * But if you were adding a value to a collection, then you could merge the two collections
	 * together so the new action had new items from both actions.
	 *
	 * @param ActionInterface $action
	 * @return ActionInterface
	 */
	public function merge(ActionInterface $other_action);
}