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

/**
 * A special modifier detected in the TriggerExecutor that stops further triggers from adding
 * their actions to the collection.
 */
class StopActionsModifier implements CollectionModifierInterface
{
	public function __construct()
	{
	}

	public function modifyCollection(ActionsCollection $collection)
	{
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return "Stop processing later triggers";
	}
}
