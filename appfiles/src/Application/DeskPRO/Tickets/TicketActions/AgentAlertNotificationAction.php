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
class AgentAlertNotificationAction implements ActionInterface
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
			return;
		}

		$online_agents = App::getEntityRepository('DeskPRO:Person')->getActiveAgents(true);

		$notify_list = array_filter($this->notify_agents, function($agent_id) use ($online_agents) {
			return in_array($agent_id, $online_agents);
		});

		if (!$notify_list) {
			return;
		}

		$is_new_ticket = false;
		$is_new_agent_reply = false;
		$is_new_user_reply = false;
		if ($this->tracker->isNewTicket()) {
			$is_new_ticket = true;
		}
		if ($this->tracker->hasNewAgentReply()) {
			$is_new_agent_reply = true;
		}
		if ($this->tracker->hasNewUserReply()) {
			$is_new_user_reply = true;
		}

		$em = App::getOrm();

		$em->beginTransaction();
		try {
			foreach ($notify_list as $agent_id) {
				$agent = App::getEntityRepository('DeskPRO:Person')->find($agent_id);

				$vars = array(
					'is_new_ticket'      => $is_new_ticket,
					'is_new_agent_reply' => $is_new_agent_reply,
					'is_new_user_reply'  => $is_new_user_reply,
					'ticket'             => $ticket,
					'agent'              => $agent,
					'performer'          => $this->tracker->getPersonPerformer(),
				);

				if ($this->notify_info[$agent->id]) {
					$vars['notify_info'] = $this->notify_info[$agent->id];
				}

				$tpl_line = App::getTemplating()->render('AgentBundle:TicketSearch:notify-row.html.twig', $vars);
				$cm = new ClientMessage();
				$cm->fromArray(array(
					'channel' => 'agent-notify.tickets',
					'data' => array(
						'type' => 'tickets',
						'ticket_id'  => $ticket->id,
						'row' => $tpl_line
					),
					'for_person' => $agent,
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
}
