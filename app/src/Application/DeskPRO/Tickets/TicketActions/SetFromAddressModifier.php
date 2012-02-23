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

class SetFromAddressModifier implements CollectionModifierInterface
{
	protected $email_address;

	public function __construct($email_address)
	{
		$this->email_address = $email_address;
	}

	public function modifyCollection(ActionsCollection $collection)
	{
		$notify_types = array();
		$notify_types[] = 'AgentNotificationAction';
		$notify_types[] = 'UserNotificationNewReplyAction';
		$notify_types[] = 'UserNotificationNewTicketAction';
		$notify_types[] = 'UserNotificationNewTicketValidatingAction';
		$notify_types[] = 'UserNotificationParticipantAction';

		foreach ($notify_types as $type) {
			if ($collection->hasActionType($type)) {
				$action = $collection->getActionType($type);
				$action->setFromAddress($this->email_address);
			}
		}
	}

	/**
	 * @return string
	 */
	public function getDescription($as_html = true)
	{
		return "Send notifications from {$this->email_address}";
	}
}
