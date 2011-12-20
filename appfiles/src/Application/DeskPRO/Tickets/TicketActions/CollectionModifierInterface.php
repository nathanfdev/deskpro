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

interface CollectionModifierInterface
{
	/**
	 * Inspect the collection and modify it
	 *
	 * @param ActionsCollection $collection
	 * @return void
	 */
	public function modifyCollection(ActionsCollection $collection);

	/**
	 * @return string
	 */
	public function getDescription();
}
