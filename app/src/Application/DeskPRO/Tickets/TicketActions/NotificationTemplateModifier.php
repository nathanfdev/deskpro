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

class NotificationTemplateModifier implements CollectionModifierInterface
{
	protected $template_suffix;

	public function __construct($template_suffix)
	{
		$this->template_suffix = $template_suffix;
	}

	public function modifyCollection(ActionsCollection $collection)
	{
		$notify_types = array();
		$notify_types[] = 'AgentNotificationNewReply';
		$notify_types[] = 'AgentNotificationNewTicket';
		$notify_types[] = 'AgentNotificationPropertyChange';

		foreach ($notify_types as $type) {
			if ($collection->hasActionType($type)) {
				$action = $collection->getActionType($type);
				$action->setTemplateSuffix($this->template_suffix);
			}
		}
	}

	/**
	 * @return string
	 */
	public function getDescription($as_html = true)
	{
		return "Use *{$this->template_suffix} email templates";
	}
}
