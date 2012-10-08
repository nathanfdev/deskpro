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

		$from_email = App::getSetting('core.default_from_email');
		$default_address = App::getDb()->fetchColumn("
			SELECT match_pattern
			FROM email_gateway_addresses
			WHERE match_type = 'exact'
			ORDER BY run_order ASC, id ASC
			LIMIT 1
		");

		if ($default_address) {
			$from_email = $default_address;
		}

		return $from_email;
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
			'notify_type' => 'updated',
			'emailed' => array()
		);

		$is_new_ticket      = false;
		$is_new_agent_reply = false;
		$is_new_user_reply  = false;

		$new_message = null;
		if ($this->tracker->isNewTicket()) {
			$this->tracker->logMessage("[AgentNotificationAction] isNewTicket");
			$change_info['notify_type'] = 'newticket';
			$tpl = $this->newticket_email_tpl;
			$is_new_ticket = true;
			$new_message = $this->tracker->getNewReply();

			if (!$new_message) {
				$new_message = App::getOrm()->getRepository('DeskPRO:TicketMessage')->getFirstTicketMessage($ticket);
			}

			if ($this->tracker->getNewAgentReply()) {
				$new_message = $this->tracker->getNewAgentReply();
				$from_name = $new_message->person->getDisplayName();
			} else {
				$from_name = $ticket->person->getDisplayName();
			}
		} elseif ($this->tracker->hasNewAgentReply()) {
			$this->tracker->logMessage("[AgentNotificationAction] hasNewAgentReply");
			$change_info['notify_type'] = 'newreply';
			$tpl = $this->newreply_agent_email_tpl;
			$is_new_agent_reply = true;
			$new_message = $this->tracker->getNewAgentReply();
			$from_name = $new_message->person->getDisplayName();
		} elseif ($this->tracker->hasNewUserReply()) {
			$this->tracker->logMessage("[AgentNotificationAction] hasNewUserReply");
			$change_info['notify_type'] = 'newreply';
			$tpl = $this->newreply_user_email_tpl;
			$is_new_user_reply = true;
			$new_message = $this->tracker->getNewUserReply();
			$from_name = $new_message->person->getDisplayName();
		} else {
			$this->tracker->logMessage("[AgentNotificationAction] Generic update");
			$tpl = $this->ticket_update_email_tpl;
			$from_name = null;

			if (App::getCurrentPerson()->getId()) {
				$from_name = App::getCurrentPerson()->getDisplayName();
			}
		}

		$agent_change = $this->tracker->getChangedProperty('agent');
		$team_change  = $this->tracker->getChangedProperty('agent_team');
		$part_change  = $this->tracker->getChangedProperty('participants');

		$tr = App::getTranslator();

		// This will generate an array of ticket logs that will be saved after notifcations are sent
		// But we call now so getting the diff for the email is easier, same logic as logs
		$ticket_logs = $this->tracker->getLogInspector()->getTicketLogs();

		$field_manager = App::getSystemService('ticket_fields_manager');
		$custom_fields = $field_manager->getDisplayArrayForObject($ticket);

		foreach ($this->notify_agents as $agent_id) {

			// Dont send an update notification to the agent for agent replies made by themselves
			if ($change_info['notify_type'] == 'newreply') {
				if ($new_message && !$this->tracker->isExtraSet('is_user_reply') && $new_message->person->getId() == $agent_id) {
					$this->tracker->logMessage("[AgentNotificationAction] Skipping notify agent $agent_id of agent message by himself");
					continue;
				}
			}

			/** @var $agent \Application\DeskPRO\Entity\Person */
			$agent = App::getEntityRepository('DeskPRO:Person')->find($agent_id);

			if (!$agent || !$agent->getPrimaryEmailAddress()) {
				$this->tracker->logMessage("[AgentNotificationAction] Bad agent: " . $agent_id);
				continue;
			}

			$agent->loadHelper('Agent');

			$type_flag = null;
			if ($change_info['notify_type'] == 'updated') {
				if ($agent_change && $agent_change['new'] && $agent_change['new']->getId() == $agent_id) {
					$type_flag = 'assigned';
				} elseif ($team_change && $team_change['new'] && $agent->getHelper('Agent')->isTeamMember($team_change['new']->getId())) {
					$type_flag = 'assigned_team';
				} elseif ($part_change) {
					$added_part = false;
					foreach ($part_change as $change) {
						if ($change['new'] && $change['new']->getId() == $agent_id) {
							$added_part = true;
							break;
						}
					}
					if ($added_part) {
						$type_flag = 'added_part';
					}
				}
			}

			if (!$type_flag) {
				$type_flag = $change_info['notify_type'];
			}

			$this->tracker->logMessage("[AgentNotificationAction] Type flag: " . $type_flag);

			$vars = array(
				'type_flag'          => $type_flag,
				'is_new_ticket'      => $is_new_ticket,
				'is_new_agent_reply' => $is_new_agent_reply,
				'is_new_user_reply'  => $is_new_user_reply,
				'action_performer'   => App::getCurrentPerson(),
				'ticket_logs'        => $ticket_logs,
				'new_message'        => $new_message,
				'agent'              => $agent,
				'custom_fields'      => $custom_fields,
			);

			if (isset($this->notify_info[$agent->id]) && $this->notify_info[$agent->id]) {
				$vars['notify_info'] = $this->notify_info[$agent->id];
			}

			$tac = TicketUtil::getTacForPerson($ticket, $agent);
			$vars['ticket'] = $ticket;
			$vars['person'] = $agent;
			$vars['tac'] = $tac;

			$ticketdisplay = new \Application\DeskPRO\Tickets\TicketDisplay($ticket, $agent);
			$vars['ticketdisplay'] = $ticketdisplay;
			$vars['messages']      = array_reverse($ticketdisplay->getMessages(), true);

			$message = App::getMailer()->createMessage();
			$message->setContextId('ticket_gateway');
			$message->setTemplate($tpl, $vars);
			$message->setTo($agent->getPrimaryEmailAddress(), $agent->getDisplayName());
			$message->getHeaders()->get('Message-ID')->setId($tac->getUniqueEmailMessageId());

			if ($is_new_ticket) {
				$max = App::getSetting('core.sendemail_attach_maxsize');
				$size = 0;
				foreach ($new_message->attachments as $attach) {
					if ($attach->is_inline) {
						continue;
					}

					$size += $attach->blob->filesize;
					if ($size > $max) {
						break;
					}

					$message->attachBlob($attach->blob);
				}
			} elseif ($is_new_agent_reply || $is_new_user_reply) {
				$new_message = \Orb\Util\Arrays::getFirstItem($vars['messages']);

				if ($new_message && $ticketdisplay->getMessageAttachments($new_message)) {
					$max = App::getSetting('core.sendemail_attach_maxsize');
					$size = 0;
					foreach ($ticketdisplay->getMessageAttachments($new_message) as $attach) {
						if ($attach->is_inline) {
							continue;
						}

						$size += $attach->blob->filesize;
						if ($size > $max) {
							break;
						}

						$message->attachBlob($attach->blob);
					}
				}
			}

			$this->tracker->logMessage("[AgentNotificationAction] From address: " . $this->getFromAddress($ticket));

			$from_address = $this->getFromAddress($ticket);
			$from_address = array($from_address => $from_address ? $from_name : $from_address);
			$message->setFrom($from_address);

			$email_time = microtime(true);
			App::getMailer()->send($message);

			$this->tracker->logMessage("[AgentNotificationAction] Email to " . $agent_id . ' ' . $agent->getPrimaryEmailAddress() . " with template $tpl (took " . sprintf("%.4f", microtime(true)-$email_time) . " s)");

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
