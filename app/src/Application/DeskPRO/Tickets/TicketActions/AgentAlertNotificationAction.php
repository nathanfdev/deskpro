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
use Application\DeskPRO\Entity\ClientMessage;

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
class AgentAlertNotificationAction extends AbstractAction
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

	public function __construct(TicketChangeTracker $tracker)
	{
		$this->tracker = $tracker;

		$notify_list = $this->tracker->getNotifyListBuilder()->getNotifyList();
		foreach ($notify_list as $agent_id => $matches) {
			$filters = array();
			foreach ($matches as $filter_info) {
				if (in_array('alert', $filter_info['types'])) {
					$filters[] = $filter_info['filter'];
				}
			}

			if ($filters) {
				$this->notify_agents[] = $agent_id;
				$this->notify_info[$agent_id] = array('filters' => $filters);
			}
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
			$this->tracker->logMessage("[AgentAlertNotificationAction] No agents");
			return;
		}

		$online_agents = App::getEntityRepository('DeskPRO:Person')->getActiveAgents(true);

		$this->tracker->logMessage("[AgentAlertNotificationAction] Matching agents: " . implode(', ', $this->notify_agents));
		$this->tracker->logMessage("[AgentAlertNotificationAction] Online agents: " . implode(', ', $online_agents));

		$notify_list = array_filter($this->notify_agents, function($agent_id) use ($online_agents) {
			return isset($online_agents[$agent_id]);
		});

		if (!$notify_list) {
			$this->tracker->logMessage("[AgentAlertNotificationAction] Matching agents, but they arent online to get browser notifications");
			return;
		}

		$is_new_ticket = false;
		$is_new_agent_reply = false;
		$is_new_agent_note = false;
		$is_new_user_reply = false;
		if ($this->tracker->isNewTicket()) {
			$is_new_ticket = true;
		}
		if ($this->tracker->hasNewAgentReply()) {
			$is_new_agent_reply = true;
			if ($this->tracker->getNewAgentReply()->is_agent_note) {
				$is_new_agent_note = true;
			}
		}
		if ($this->tracker->hasNewUserReply()) {
			$is_new_user_reply = true;
		}

		$em = App::getOrm();

		$log_items = $this->getLogItems();

		$em->beginTransaction();
		try {
			foreach ($notify_list as $agent_id) {
				$this->tracker->logMessage("[AgentAlertNotificationAction] Sent to $agent_id");
				$agent = App::getEntityRepository('DeskPRO:Person')->find($agent_id);

				if (!$agent) {
					$this->tracker->logMessage("[AgentNotificationAction] Bad agent: " . $agent_id);
					continue;
				}

				if (!$agent->PermissionsManager->TicketChecker->canView($ticket)) {
					$this->tracker->logMessage("[AgentNotificationAction] Skipping notify agent $agent_id (noperm)");
					continue;
				}

				$vars = array(
					'is_new_ticket'      => $is_new_ticket,
					'is_new_agent_reply' => $is_new_agent_reply,
					'is_new_agent_note'  => $is_new_agent_note,
					'is_new_user_reply'  => $is_new_user_reply,
					'ticket'             => $ticket,
					'agent'              => $agent,
					'performer'          => $this->tracker->getPersonPerformer(),
					'log_items'          => $log_items,
				);

				if ($is_new_ticket) {
					$vars['performer'] = $ticket->person;
				} elseif ($is_new_agent_reply || $is_new_user_reply) {
					$message = $this->tracker->getNewReply();
					if ($message) {
						$vars['performer'] = $message->person;
					}
				}

				if (!empty($this->notify_info[$agent->id])) {
					$vars['notify_info'] = $this->notify_info[$agent->id];
				}

				$tpl_line = App::getTemplating()->render('AgentBundle:TicketSearch:notify-row.html.twig', $vars);
				$cm = new ClientMessage();
				$cm->fromArray(array(
					'channel' => 'agent-notify.tickets',
					'data' => array(
						'type'       => 'tickets',
						'ticket_id'  => $ticket->id,
						'row'        => $tpl_line
					),
					'for_person'        => $agent,
					'created_by_client' => 'sys'
				));
				$em->persist($cm);
			}

			$em->flush();
			$em->commit();
		} catch (\Exception $e) {
			$em->rollback();
			throw $e;
		}
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

	protected function getLogItems()
	{
		$ticket_logs = $this->tracker->getLogInspector()->getTicketLogs();
		return $ticket_logs;
	}
}
