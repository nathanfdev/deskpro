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
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Notifications\AgentNotifyListBuilder;
use Orb\Util\CheckedOptionsArray;

/**
 * Send browser alerts. Note that this action isn't meant to be used with a trigger,
 * so it's called manually as part of TicketManager.
 *
 * The reason is that it needs info about the ticket logs so alerts can be properly dismissed
 * in the interface.
 *
 * @option array agent_ids      Agents to send to
 * @option TicketLog[]          ticket_logs
 */
class SendAgentAlert extends AbstractContainerAwareAction implements ActionInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('agent_ids', 'ticket_logs');
		return $options;
	}

	/**
	 * @param Ticket $ticket
	 * @param array $agent_ids
	 * @param ExecutorContextInterface $context
	 * @return array
	 */
	private function resolveAgents(Ticket $ticket, array $agent_ids, ExecutorContextInterface $context)
	{
		$agents = array();

		foreach ($agent_ids as $aid) {
			// -1 = current user
			if ($aid == -1) {
				if ($context->getPersonContext() && $context->getPersonContext()->is_agent) {
					$agents[] = $context->getPersonContext();
				}

			// assigned agent
			} else if ($aid == 'agent') {
				if ($ticket->agent) {
					$agents[] = $ticket->agent;
				}

			// agents of assigned team
			} else if ($aid == 'team') {
				if ($ticket->agent_team) {
					foreach ($ticket->agent_team->members as $agent) {
						$agents[] = $agent;
					}
				}

			// followers
			} else if ($aid == 'followers') {
				if ($agent_followers = $ticket->getAgentParticipants()) {
					foreach ($agent_followers as $agent) {
						$agents[] = $agent;
					}
				}

			// based on notify list
			} else if ($aid == 'notify_list') {

				if ($isNotificationsDisabled) continue;

				$change_detect = $this->getContainer()->getTicketFilterChangeDetector();
				$change_set    = $change_detect->getFilterChangeSet($ticket, $context);
				$list_builder  = new AgentNotifyListBuilder(
					$ticket,
					$change_set,
					$this->getContainer()->getEm()->getRepository('DeskPRO:TicketFilterSubscription')
				);
				$list_builder->setLogger($context->getLogger());

				$notify = $list_builder->genNotifyList();

				$person_context = $context->getPersonContext();
				foreach ($notify as $n) {
					// dont send to self
					if ($person_context && $person_context === $n['agent']) {
						$context->getLogger()->debug("[SendAgentAlert] notify_list skipping self");
						continue;
					}
					if (in_array('alert', $n['types'])) {
						$agents[] = $n['agent'];
					}
				}

			// specific agents
			} else {
				if ($agent = $this->getContainer()->getAgentData()->get($aid)) {
					$agents[] = $agent;
				}
			}
		}

		if (!$agents) {
			return array();
		}

		$agents = array_unique($agents);

		return $agents;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$context->getLogger()->debug("[SendAgentAlert] Begin :: agent_ids = " . implode(', ', $this->getActionOption('agent_ids')));
		$start_time = microtime(true);

		$agents = $this->resolveAgents($ticket, $this->getActionOption('agent_ids'), $context);

		if (!$agents) {
			$context->getLogger()->debug("[SendAgentAlert] No agents to send to");
			return;
		}

		$vars = array(
			'is_new_ticket'      => $ticket->getStateChangeRecorder()->isNewTicket(),
			'is_new_agent_reply' => $ticket->getStateChangeRecorder()->hasNewAgentReply(),
			'is_new_agent_note'  => $ticket->getStateChangeRecorder()->hasNewAgentNote(),
			'is_new_user_reply'  => $ticket->getStateChangeRecorder()->hasNewUserReply(),
			'ticket'             => $ticket,
			'performer'          => $context->getPersonContext(),
			'log_items'          => $this->getActionOption('ticket_logs'),
		);

		$log_ids = array_map(function($l) { return $l->id; }, $vars['log_items']);
		$alert_sender = $this->getContainer()->getAgentAlertSender();

		$alert_data = array(
			'@fetch_types'       => array('ticket' => 'DeskPRO:Ticket', 'performer' => 'DeskPRO:Person', 'log_items' => 'DeskPRO:TicketLog'),
			'ticket'             => $ticket->getId(),
			'performer'          => $vars['performer'] ? $vars['performer']->id : 0,
			'is_new_ticket'      => $vars['is_new_ticket'],
			'is_new_agent_reply' => $vars['is_new_agent_reply'],
			'is_new_agent_note'  => $vars['is_new_agent_note'],
			'is_new_user_reply'  => $vars['is_new_user_reply'],
			'log_items'          => $log_ids,
		);

		$sent_count = 0;
		$em  = $this->getContainer()->getEm();
		$tpl = $this->getContainer()->getTemplating();

		foreach ($agents as $agent) {
			if (!$agent->PermissionsManager->TicketChecker->canView($ticket)) {
				continue;
			}

			$vars['agent'] = $agent;

			if (!empty($this->notify_info[$agent->id])) {
				$vars['notify_info'] = $this->notify_info[$agent->id];
			}

			$tpl_line = $tpl->render('AgentBundle:TicketSearch:notify-row.html.twig', $vars);
			$alert_data['browser_rendered'] = $tpl_line;

			$alert = $alert_sender->createAlert($agent, 'tickets', $alert_data);

			if ($alert) {
				$sent_count++;

				$em->persist($alert);

				$cm = $alert_sender->createClientMessage($agent, 'tickets', $alert_data, $alert);
				if ($cm) {
					$em->persist($cm);
				}
			}
		}

		$context->getLogger()->info(sprintf("[SendAgentAlert] Sent %d alerts in %.3fs", $sent_count, microtime(true)-$start_time));
	}
}