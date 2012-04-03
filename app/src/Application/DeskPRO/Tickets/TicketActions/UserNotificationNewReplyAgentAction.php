<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tickets
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\Translate\DelegatePhrase;
use Application\DeskPRO\App;

class UserNotificationNewReplyAgentAction extends AbstractUserNotificationAction
{
	/**
	 * @var string
	 */
	protected $newreply_agent_email_tpl = 'DeskPRO:emails_agent:new-reply-agent.html.twig';

	/**
	 * @param string $tpl
	 */
	public function setEmailTemplate($tpl, $type = '')
	{
		switch ($tpl) {
			case 'user_new_reply_agent':
				$this->newreply_agent_email_tpl = $tpl;
				break;
		}
	}

	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$change_info = array(
			'type' => 'user_notify',
			'notify_type' => 'agent_reply',
			'emailed' => array(),
			'cced' => array()
		);

		$messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages($ticket,array(
			'limit' => 20,
			'order' => 'DESC',
			'with_notes' => false
		));

		$new_message = \Orb\Util\Arrays::getFirstItem($messages);

		$vars = array(
			'action' => 'new_agent_reply',
			'new_message' => $new_message,
			'messages' => $messages,
			'ticket' => $ticket
		);

		$this->doSend($this->newreply_agent_email_tpl, $vars, $ticket, $change_info);
		$this->tracker->recordMultiPropertyChanged('log_actions', null, $change_info);
	}

	/**
	 * @return string
	 */
	public function getDescription($as_html = true)
	{
		return '';
	}
}
