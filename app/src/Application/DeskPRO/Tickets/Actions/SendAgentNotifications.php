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
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketDisplay;
use Application\DeskPRO\Tickets\TicketEmail;
use Orb\Util\Arrays;
use Application\DeskPRO\Tickets\Util as TicketUtil;
use Orb\Util\CheckedOptionsArray;

/**
 * Send an email to the user
 *
 * @option bool template          The template to use when sending (0/false for default)
 * @option bool from_name         Who to send the email from
 * @option bool force_agent_ids   Array of IDs to always send to, even if the agent doesnt have a subscription
 */
class SendAgentNotifications extends AbstractAction implements ActionInterface, NoopableInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addValidNames('template', 'from_name', 'force_agent_ids');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		list ($email_agents, $alert_agents, $notify_info) = $this->buildNotifyLists($ticket, $context);

		$context->getLogger()->info(sprintf("[SendAgentNotifications] %d emails and %d alerts to send", $email_agents, $alert_agents));
		if (!$email_agents && !$alert_agents) {
			return;
		}

		#------------------------------
		# Build up some type flags
		#------------------------------

		$state = $ticket->getStateChangeRecorder();
		if ($state->isNewTicket()) {
			$type = 'newticket';
		} else if ($state->hasChangedField('messages')) {
			$type = 'newreply';
		} else {
			$type = 'updated';
		}

		$context->getLogger()->info("[SendAgentNotifications] Type: $type");
		$context->getLogger()->info(sprintf("[SendAgentNotifications] Performer: %s", $context->getEventPerformer()));

		$agent_change = null;
		$team_change  = null;
		$part_change  = null;
		$part_added_ids = array();

		if ($state->hasChangedField('agent')) {
			$agent_change = $state->getCombinedChangeForField('agent');
		}
		if ($state->hasChangedField('agent_team')) {
			$team_change = $state->getCombinedChangeForField('agent_team');
		}
		if ($state->hasChangedField('participants')) {
			$part_change = $state->getCombinedChangeForField('participants');
			foreach ($part_change->getNew() as $part) {
				$part_added_ids[$part->person->id] = true;
			}
		}

		#------------------------------
		# Set reply flags
		#------------------------------

		$new_replies = $state->getNewReplies();
		$is_new_ticket      = $state->isNewTicket();
		$is_new_agent_reply = false;
		$is_new_agent_note  = false;
		$is_new_user_reply  = false;

		// Dont notify self of own replies
		foreach ($new_replies as $message) {
			if ($message->is_agent_note) {
				$is_new_agent_note = true;
			} else if ($message->person->is_agent) {
				$is_new_agent_reply = true;
			} else {
				$is_new_user_reply = true;
			}

			$pid = $message->person->id;
			if (isset($notify_info[$pid]) && !$notify_info[$pid]['with_force']) {
				unset($email_agents[$pid]);
				unset($alert_agents[$pid]);
				unset($notify_info[$pid]);
			}
		}

		#------------------------------
		# Sort out which template to use for 'default'
		#------------------------------

		$template = $this->getActionOption('template');
		if (!$template || $template == false || $template == 0) {
			switch ($type) {
				case 'newticket': $template = 'DeskPRO:emails_agent:new-ticket.html.twig'; break;
				case 'updated': $template = 'DeskPRO:emails_agent:ticket-update.html.twig'; break;
				case 'newreply':
					if ($is_new_agent_note || $is_new_agent_note) {
						$template = 'DeskPRO:emails_agent:new-reply-agent.html.twig';
					} else {
						$template = 'DeskPRO:emails_agent:new-reply-user.html.twig';
					}
			}
		}

		#------------------------------
		# Build map of mentions
		#------------------------------

		$mention_agents = array();
		foreach ($notify_info as $agent_id => $info) {
			if ($info['with_mention']) {
				$mention_agents[$agent_id] = $info['agent'];
			}
		}

		#------------------------------
		# Send email notifications
		#------------------------------

		foreach ($email_agents as $agent) {
			$type_flag = $type;
			if ($type == 'updated') {
				if ($agent_change && $agent_change->getNew() && $agent_change->getNew()->getId() == $agent->id) {
					$type_flag = 'assigned';
				} elseif ($team_change && $team_change->getNew() && $agent->getHelper('Agent')->isTeamMember($team_change->getNew()->getId())) {
					$type_flag = 'assigned_team';
				} elseif ($part_change && isset($part_added_ids[$agent->id])) {
					$type_flag = 'added_part';
				}
			}

			if ($type_flag == 'updated' && $state->hasChangedField('status')) {
				$type_flag = 'status_changed';
			}

			$vars = array(
				'type'               => $type,
				'type_flag'          => $type_flag,
				'is_new_ticket'      => $is_new_ticket,
				'is_new_agent_reply' => $is_new_agent_reply,
				'is_new_agent_note'  => $is_new_agent_note,
				'is_new_user_reply'  => $is_new_user_reply,
				'action_performer'   => $context->getPersonContext(),
				'new_message'        => Arrays::getLastItem($new_replies),
				'new_messages'       => $new_replies,
				'mention_agents'     => $mention_agents,
				'is_my_mention'      => isset($mention_agents[$agent->id]),
				'agent'              => $agent,
				'notify_info'        => $notify_info[$agent->id],
			);

			$ticket_email = new TicketEmail($ticket, $agent, 'agent', $template);
			$ticket_email->send($context, $vars);
		}

		#------------------------------
		# Send alerts
		#------------------------------

		$tpl = $context->getContainer()->getTemplating();
		$alert_sender = $context->getContainer()->getAgentAlertSender();

		foreach ($alert_agents as $agent) {
			$vars = array(
				'type'               => $type,
				'type_flag'          => $type_flag,
				'is_new_ticket'      => $is_new_ticket,
				'is_new_agent_reply' => $is_new_agent_reply,
				'is_new_agent_note'  => $is_new_agent_note,
				'is_new_user_reply'  => $is_new_user_reply,
				'action_performer'   => $context->getPersonContext(),
				'mention_agents'     => $mention_agents,
				'is_my_mention'      => isset($mention_agents[$agent->id]),
				'agent'              => $agent,
				'notify_info'        => $notify_info[$agent->id],
			);

			$tpl_line = $tpl->render('AgentBundle:TicketSearch:notify-row.html.twig', $vars);
			$alert_sender->send($agent, 'tickets', array(
				'@fetch_types'       => array('ticket' => 'DeskPRO:Ticket', 'performer' => 'DeskPRO:Person', 'log_items' => 'DeskPRO:TicketLog'),
				'browser_rendered'   => $tpl_line,
				'ticket'             => $ticket->getId(),
				'performer'          => $vars['action_performer'],
				'is_new_ticket'      => $is_new_ticket,
				'is_new_agent_reply' => $is_new_agent_reply,
				'is_new_agent_note'  => $is_new_agent_note,
				'is_new_user_reply'  => $is_new_user_reply,
				'log_items'          => array(),
			));
		}
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 * @return array
	 */
	private function buildNotifyLists(Ticket $ticket, ExecutorContextInterface $context)
	{
		$list_builder = $context->createNotifyListBuilder($ticket);

		$email_agents = array();
		$alert_agents = array();
		$notify_info  = array();

		$init_notify_info = function(Person $agent) use (&$notify_info) {
			if (!isset($notify_info[$agent->id])) {
				$notify_info[$agent->id] = array(
					'agent'         => $agent,
					'email_filters' => array(),
					'alert_filters' => array(),
					'with_mention'  => false,
					'with_force'    => false
				);
			}
		};

		foreach ($list_builder->genNotifyList() as $notify) {
			$email_filters = array();
			$alert_filters = array();

			$agent = $notify['agent'];
			if (!$agent->PermissionsManager->TicketChecker->canView($ticket)) {
				continue;
			}

			foreach ($notify['filter_subs'] as $filter_info) {
				if (in_array('email', $filter_info['types'])) {
					$email_filters[] = $filter_info['filter'];
				}
				if (in_array('alert', $filter_info['types'])) {
					$alert_filters[] = $filter_info['filter'];
				}
			}

			if ($email_filters && $agent->getPrimaryEmailAddress()) {
				$email_agents[$agent->id] = $agent;
				$init_notify_info($agent);
				$notify_info[$agent->id]['email_filters'][] = $email_filters;
			}
			if ($alert_filters) {
				$alert_agents[$agent->id] = $notify['agent'];
				$init_notify_info($agent);
				$notify_info[$agent->id]['alert_filters'][] = $alert_filters;
			}
		}

		foreach ($this->getActionOption('force_agent_ids') as $agent_id) {
			if ($agent_id == -1) {
				if (!$ticket->agent) {
					continue;
				}
				$agent_id = $ticket->agent->id;
			}

			$agent = $context->getContainer()->getAgentData()->get($agent_id);
			if (!$agent->PermissionsManager->TicketChecker->canView($ticket)) {
				$agent = null;
			}

			if ($agent) {
				if ($agent->getPrimaryEmailAddress()) {
					$email_agents[$agent->id] = $agent;
				}

				$alert_agents[$agent->id] = $agent;
				$init_notify_info($agent);
				$notify_info[$agent->id]['with_force'] = true;
			}
		}

		if ($context->getVars()->get('mention_agent_ids')) {
			foreach ($context->getVars()->get('mention_agent_ids') as $agent_id) {
				$agent = $context->getContainer()->getAgentData()->get($agent_id);
				if (!$agent->PermissionsManager->TicketChecker->canView($ticket)) {
					$agent = null;
				}

				if ($agent) {
					if ($agent->getPrimaryEmailAddress()) {
						$email_agents[$agent->id] = $agent;
					}

					$alert_agents[$agent->id] = $agent;

					$init_notify_info($agent);
					$notify_info[$agent->id]['with_mention'] = true;
				}
			}
		}

		return array(
			$email_agents,
			$alert_agents,
			$notify_info
		);
	}


	/**
	 * {@inheritDoc}
	 */
	public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
	{
		if ($ticket->status == 'hidden' || $context->getVars()->get('mute_agent_emails')) {
			return true;
		}

		return false;
	}
}