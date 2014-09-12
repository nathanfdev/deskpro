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
use Application\DeskPRO\ORM\StateChange\ChangeCollection;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Notifications\AgentNotifyListBuilder;
use Application\DeskPRO\Tickets\TicketEmailBuilder;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\CheckedOptionsArray;

/**
 * Send an email to one or more agents
 *
 * @option bool template       The template to send
 * @option bool agent_ids      Agents to send to
 * @option bool from_name      Who to send the email from
 * @option bool from_account   The account to send from (falsey for ticket account)
 */
class SendAgentEmail extends AbstractEmailAction implements ActionInterface, NoopableInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('agent_ids');
		$options->addValidNames('template', 'from_name', 'from_account');
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

		$person_context = $context->getPersonContext();
		$isNotificationsDisabled = $this->getContainer()->getSetting('agent.disable_notifications');

		foreach ($agent_ids as $aid) {
			if ($aid == 'notify_list') {

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

				foreach ($notify as $n) {
					// dont send to self
					if ($person_context && $person_context === $n['agent']) {
						$override = false;
						if ($person_context->getPref('agent_notify_override.all.email')) {
							$override = true;
						} else if ($person_context->getPref('agent_notify_override.forward.email') && $context->getEventType() == 'newticket' && $context->getEventMethod() == 'email') {
							$override = true;
						}

						if (!$override) {
							$context->getLogger()->debug("[SendAgentEmail] notify_list skipping self");
							continue;
						} else {
							$context->getLogger()->debug("[SendAgentEmail] notify_list sending to self because got override preference");
						}
					}
					if (in_array('email', $n['types'])) {
						$agents[] = $n['agent'];
					}
				}

				$force_list = $context->getVars()->get('agent_force_subscription_list', array());
				if ($force_list) {
					$context->getLogger()->debug("[SendAgentEmail] Appending force list");
					$agents = array_merge($agents, $force_list);
				}

			} else {
				$agent_data = $this->getContainer()->getAgentData();
				$agents = array_merge($agents, $agent_data->selectAgents($aid, $person_context, $ticket));
			}
		}

		if (!$agents) {
			return array();
		}

		$agents = array_unique($agents);
		$agents = array_filter($agents, function($a) { return $a->is_agent && !$a->is_deleted && !$a->is_disabled; });

		return $agents;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$context->getLogger()->debug("[SendAgentEmail] Begin :: agent_ids = " . implode(', ', $this->getActionOption('agent_ids')));
		$start_time = microtime(true);

		$agents = $this->resolveAgents($ticket, $this->getActionOption('agent_ids'), $context);

		if (!$agents) {
			$context->getLogger()->debug("[SendAgentEmail] No agents to send to");
			return;
		}

		try {
			$from_account = $this->getFromEmailAccountOption($ticket, $context);
		} catch (\InvalidArgumentException $e) {
			$context->getLogger()->warn("[SendAgentEmail] Error {$e->getMessage()}");
			return;
		}

		try {
			$template = $this->getEmailTemplateOption($ticket, $context, false);
		} catch (\InvalidArgumentException $e) {
			$context->getLogger()->warn("[SendAgentEmail] Error {$e->getMessage()}");
			return;
		}

		#-------------------------
		# Vars
		#-------------------------

		$default_vars = $this->getStandardEmailVars($ticket, $context, 'agent');

		#-------------------------
		# Send emails
		#-------------------------

		$sent_count = 0;

		$state = $ticket->getStateChangeRecorder();
		$fn_check_new_part = function($agent) use ($state, $ticket) {
			$has = false;
			foreach ($ticket->participants as $p) {
				if ($p->person === $agent) {
					$has = true;
					break;
				}
			}
			if (!$has) {
				return false;
			}

			foreach ($state->getChangesForField('participants') as $change) {
				if ($change instanceof ChangeCollection) {
					foreach ($change->getAddedElements() as $p) {
						if ($p->person === $agent) {
							return true;
						}
					}
				}
			}

			return false;
		};

		foreach ($agents as $agent) {
			$sent_count++;

			$context->getLogger()->debug(sprintf("[SendAgentEmail] Sending to <Person:%d> %s", $agent->id, $agent->getDisplayName()));

			$vars = $default_vars;

			$type_flag = null;
			if ($state->hasChangedField('agent') && $ticket->agent && $ticket->agent === $agent) {
				$type_flag = 'assigned';
			} else if ($state->hasChangedField('agent_team') && $ticket->agent_team && $agent->getHelper('Agent')->isTeamMember($ticket->agent_team->id)) {
				$type_flag = 'assigned_team';
			} else if ($state->hasChangedField('participants') && $fn_check_new_part($agent)) {
				$type_flag = 'added_part';
			} else if ($state->hasChangedField('status')) {
				$type_flag = 'status_changed';
			}

			$vars['type_flag'] = $type_flag;

			$ticket_email = TicketEmailBuilder::createFromContainer($this->getContainer())
				->setTicket($ticket)
				->setToPerson($agent)
				->setFromName($this->renderFromName($this->getActionOption('from_name'), $ticket, $context, 'agent'))
				->setFromEmailAccount($from_account)
				->setAgentMode()
				->setTemplateName($template)
				->setMaxAttachSize($this->getContainer()->getSetting('core.sendemail_attach_maxsize'))
				->setLogger($context->getLogger())
				->buildTicketEmail();

			try {
				$ticket_email->send($vars);
				$this->recordEmailTicketLog($ticket_email, $ticket, $context);
			} catch (\Exception $e) {
				$context->getLogger()->error(
					sprintf("[SendAgentEmail] Exception: [%s] %s", $e->getCode(), $e->getMessage()),
					array('exception' => $e)
				);

				throw $e;
			}
		}

		$context->getLogger()->info(sprintf("[SendAgentEmail] Send %d messages in %.3fs", $sent_count, microtime(true)-$start_time));
	}


	/**
	 * {@inheritDoc}
	 */
	public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$this->getContainer()->getEmailAccountManager()->countOutgoingAccounts()) {
			$context->getLogger()->debug("[SendUserEmail] no outgoing email accounts are defined");
			return true;
		}

		if ($context->getVars()->get('mute_agent_emails')) {
			$context->getLogger()->debug("[SendAgentEmail] mute_agent_emails = true");
			return true;
		}

		return false;
	}
}