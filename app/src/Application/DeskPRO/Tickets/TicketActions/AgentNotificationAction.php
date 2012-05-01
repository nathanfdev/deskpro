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
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

use Application\DeskPRO\Email\TicketUtil;
use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\App;

use \Application\DeskPRO\Translate\DelegatePhrase;

/**
 * An agent notification sends emails to agents when a ticket in one
 * of their subscribed filters is affected.
 *
 * This is a built-in action, it cannot be added via the trigger interface.
 * (But it can be modified)
 */
class AgentNotificationAction extends AbstractAction
{
	/**
	 * @var \Application\DeskPRO\Tickets\TicketChangeTracker
	 */
	protected $tracker;

	/**
	 * @var array
	 */
	protected $notify_agents = array();

	/**
	 * @var array
	 */
	protected $notify_info = array();

	/**
	 * @var string
	 */
	protected $newticket_email_tpl = 'DeskPRO:emails_agent:new-ticket.html.twig';

	/**
	 * @var string
	 */
	protected $newreply_user_email_tpl = 'DeskPRO:emails_agent:new-reply-user.html.twig';

	/**
	 * @var string
	 */
	protected $newreply_agent_email_tpl = 'DeskPRO:emails_agent:new-reply-agent.html.twig';

	/**
	 * @var string
	 */
	protected $ticket_update_email_tpl = 'DeskPRO:emails_agent:ticket-update.html.twig';

	/**
	 * @var string
	 */
	protected $from_address;

	public function __construct(TicketChangeTracker $tracker)
	{
		$this->tracker = $tracker;

		$notify_list = $this->tracker->getNotifyListBuilder()->getNotifyList();
		foreach ($notify_list as $agent_id => $matches) {
			$filters = array();
			foreach ($matches as $filter_info) {
				if (in_array('email', $filter_info['types'])) {
					$filters[] = $filter_info['filter'];
				}
			}

			if ($filters) {
				$this->notify_agents[] = $agent_id;
				$this->notify_info[$agent_id] = array('filters' => $filters);
			}
		}
	}

	public function getFromAddress(Ticket $ticket)
	{
		if ($ticket->notify_email) {
			return $ticket->notify_email;
		}

		return App::getSetting('core.default_from_email');
	}

	/**
	 * @param string $tpl
	 */
	public function setEmailTemplate($tpl, $type = '')
	{
		switch ($tpl) {
			case 'agent_new_ticket':
				$this->newticket_email_tpl = $tpl;
				break;
			case 'agent_new_reply_agent':
				$this->newreply_agent_email_tpl = $tpl;
				break;
			case 'agent_new_reply_user':
				$this->newreply_user_email_tpl = $tpl;
				break;
		}
	}

	/**
	 * Add additional agents to notify.
	 * This is used by any modifiers
	 *
	 * @return
	 */
	public function addAdditionalAgents($codes)
	{
		$agent_ids = array();
		$ticket = $this->tracker->getTicket();

		if (!is_array($codes)) {
			$codes = array($codes);
		}

		foreach ($codes as $send_to) {
			if ($send_to == 'assigned_agent') {
				if ($ticket['agent_id']) $agent_ids[] = $ticket['agent_id'];

			} elseif ($send_to == 'assigned_agent_team') {
				if ($ticket['agent_team_id']) {
					$agent_ids = array_merge($agent_ids, App::getEntityRepository('DeskPRO:AgentTeam')->getMemberIds($ticket['agent_team_id']));
				}

			} elseif (strpos($send_to, 'agent.') === 0) {
				list (, $agent_id) = explode('.', $send_to, 2);
				$agent_ids[] = $agent_id;

			} elseif (strpos($send_to, 'agent_team.') === 0) {
				list (, $agent_team_id) = explode('.', $send_to, 2);
				$agent_ids = array_merge($agent_ids, App::getEntityRepository('DeskPRO:AgentTeam')->getMemberIds($agent_team_id));
			}
		}

		if ($agent_ids) {
			$this->notify_agents = array_merge($this->notify_agents, $agent_ids);
			$this->notify_agents = array_unique($this->notify_agents);
		}
	}

	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		if (!$this->notify_agents) {
			$this->tracker->logMessage("[AgentNotificationAction] No agents");
			return;
		}

		$this->tracker->logMessage("[AgentNotificationAction] Agents: " . implode(', ', $this->notify_agents));

		$change_info = array(
			'type' => 'agent_notify',
			'notify_type' => 'ticketnofity',
			'emailed' => array()
		);

		$subject_phrase = 'agent.emails.subject_ticket_updated';
		$tpl = $this->ticket_update_email_tpl;
		$is_new_ticket = false;
		$is_new_agent_reply = false;
		$is_new_user_reply = false;
		if ($this->tracker->isNewTicket()) {
			$change_info['notify_type'] = 'newticket';
			$tpl = $this->newticket_email_tpl;
			$is_new_ticket = true;
		} elseif ($this->tracker->hasNewAgentReply()) {
			$change_info['notify_type'] = 'newreply';
			$tpl = $this->newreply_agent_email_tpl;
			$is_new_agent_reply = true;
		} elseif ($this->tracker->hasNewUserReply()) {
			$change_info['notify_type'] = 'newreply';
			$tpl = $this->newreply_user_email_tpl;
			$is_new_user_reply = true;
		}

		$tr = App::getTranslator();

		$messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages($ticket,array(
			'limit' => 25,
			'order' => 'DESC',
			'with_notes' => true
		));

		foreach ($this->notify_agents as $agent_id) {
			$agent = App::getEntityRepository('DeskPRO:Person')->find($agent_id);

			if (!$agent || !$agent->getPrimaryEmailAddress()) {
				$this->tracker->logMessage("[AgentNotificationAction] Bad agent: " . $agent_id);
				continue;
			}

			$vars = array(
				'email_subject' => new DelegatePhrase($subject_phrase, array('ticket_subject' => $ticket['subject'])),
				'is_new_ticket' => $is_new_ticket,
				'is_new_agent_reply' => $is_new_agent_reply,
				'is_new_user_reply' => $is_new_user_reply,
			);

			if ($this->notify_info[$agent->id]) {
				$vars['notify_info'] = $this->notify_info[$agent->id];
			}

			$tac = TicketUtil::getTacForPerson($ticket, $agent);
			$vars['ticket'] = $ticket;
			$vars['person'] = $agent;
			$vars['tac'] = $tac;
			$vars['messages'] = $messages;

			$message = App::getMailer()->createMessage();
			$message->setTemplate($tpl, $vars);
			$message->setTo($agent->getPrimaryEmailAddress(), $agent->getDisplayName());
			$message->getHeaders()->get('Message-ID')->setId($tac->getUniqueEmailMessageId());
			$message->setFrom($this->getFromAddress($ticket));

			$email_time = microtime(true);
			App::getMailer()->send($message);

			$this->tracker->logMessage("[AgentNotificationAction] Email: " . $agent_id . ' ' . $agent->getPrimaryEmailAddress());

			$change_info['emailed'][] = $agent;
		}

		$this->tracker->recordMultiPropertyChanged('log_actions', null, $change_info);
	}

	/**
	 * This is a built-in trigger, merging should never happen.
	 *
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
		return '';
	}
}
