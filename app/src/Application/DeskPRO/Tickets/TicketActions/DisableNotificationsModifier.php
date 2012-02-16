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

class DisableNotificationsModifier implements CollectionModifierInterface
{
	public function modifyCollection(ActionsCollection $collection)
	{
		$notify_types = array();
		$notify_types[] = 'AgentNotificationNewReply';
		$notify_types[] = 'AgentNotificationNewTicket';
		$notify_types[] = 'AgentNotificationPropertyChange';

		foreach ($notify_types as $type) {
			if ($collection->hasActionType($type)) {
				$collection->removeActionType($type);
			}
		}
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return 'Disable all notifications';
	}
}
