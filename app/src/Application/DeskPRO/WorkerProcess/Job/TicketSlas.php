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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Mail\QueueProcessor\Database as DatabaseQueueProcessor;

/**
 * Handles SLA warn/fail updates
 */
class TicketSlas extends AbstractJob
{
	const DEFAULT_INTERVAL = 60;

	public function run()
	{
		//TODO part of trigger redo
		return;

		$GLOBALS['DP_ESCALATION_RUNNING'] = true;

		$em = App::getOrm();

		$count_failed = 0;
		$count_warning = 0;

		$ticket_slas = App::getEntityRepository('DeskPRO:TicketSla')->getTicketSlasPastThreshold('fail');
		foreach ($ticket_slas as $ticket_sla) {
			$ticket_sla->evaluateSlaDates();
			$em->persist($ticket_sla);
			$em->flush();

			if ($ticket_sla->sla_status == \Application\DeskPRO\Entity\TicketSla::STATUS_FAIL) {
				$count_failed++;
			}
		}

		App::getOrm()->clear('Application\\DeskPRO\\Entity\\Ticket');
		App::getOrm()->clear('Application\\DeskPRO\\Entity\\TicketSla');

		$ticket_slas = App::getEntityRepository('DeskPRO:TicketSla')->getTicketSlasPastThreshold('warning');
		foreach ($ticket_slas as $ticket_sla) {
			$ticket_sla->evaluateSlaDates();
			$em->persist($ticket_sla);
			$em->flush();

			if ($ticket_sla->sla_status == \Application\DeskPRO\Entity\TicketSla::STATUS_WARNING) {
				$count_warning++;
			}
		}

		App::getOrm()->clear('Application\\DeskPRO\\Entity\\Ticket');
		App::getOrm()->clear('Application\\DeskPRO\\Entity\\TicketSla');

		if ($count_warning || $count_failed) {
			$this->getLogger()->logInfo("SLA statuses updated. Failed: $count_failed, warning: $count_warning");
		}

		unset($GLOBALS['DP_ESCALATION_RUNNING']);
	}
}

/*
public function evaluateSlaDates($call_triggers = true)
	{
		if ($this->is_completed) {
			return;
		}

		$time = $this->sla->getSlaTestTime($this->ticket);

		if ($this->sla_status == self::STATUS_OK && $this->warn_date && $this->warn_date->getTimestamp() < $time) {
			$this->setSlaStatus(self::STATUS_WARNING, $call_triggers);
		}

		if (in_array($this->sla_status, array(self::STATUS_OK, self::STATUS_WARNING)) && $this->fail_date && $this->fail_date->getTimestamp() < $time) {
			$this->setSlaStatus(self::STATUS_FAIL, $call_triggers);
		}
	}

private function _getTestTicketDateField(Ticket $ticket)
{
	$times = array(time());

	if ($this->sla_type == self::TYPE_FIRST_RESPONSE && $ticket->date_last_agent_reply) {
		if ($ticket->date_last_agent_reply->getTimestamp() > $ticket->date_created->getTimestamp()) {
			// don't auto resolve sla on ticket creation, even if created by an agent
			$times[] = $ticket->date_first_agent_reply->getTimestamp();
		}
	}

	if ($ticket->date_closed) {
		$times[] = $ticket->date_closed->getTimestamp();
	}

	if ($ticket->status == 'resolved' && $ticket->date_resolved) {
		$times[] = $ticket->date_resolved->getTimestamp();
	}

	return min($times);
}
public function calculateSlaDates($call_triggers = true)
	{
		if (!$this->is_completed) {
			$this->setModelField('warn_date', $this->sla->calculateWarnDate($this->ticket));
			$this->setModelField('fail_date', $this->sla->calculateFailDate($this->ticket));
			$this->evaluateSlaDates($call_triggers);

			$completed_ts = $this->sla->calculateCompleted($this->ticket);
			if ($completed_ts) {
				$this->setIsCompleted(true, $completed_ts);
			} else {
				$this->setIsCompleted(false);
			}
		}
	}



	protected function _runTrigger(TicketTrigger $trigger = null, $status, \DateTime $date = null)
	{
		if (!$trigger) {
			return;
		}

		$tracker = $this->ticket->getTicketLogger();
		if ($tracker) {
			$tracker->recordExtraMulti('trigger', $trigger);
			$tracker->recordExtra('sla', $this->sla);
			$tracker->recordExtra('sla_status', $status);
		}

		if ($this->ticket->id) {
			$trigger_log = array(
				'ticket_id'     => $this->ticket->id,
				'trigger_id'    => $trigger->id,
				'date_ran'      => date('Y-m-d H:i:s'),
				'date_criteria' => ($date ? $date->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'))
			);
			App::getDb()->insert('ticket_trigger_logs', $trigger_log);
		}

		$factory = new \Application\DeskPRO\Tickets\TicketActions\ActionsFactory();
		if ($tracker) {
			$factory->addGlobalOption('tracker', $tracker);
		}
		$factory->addGlobalOption('ticket', $this->ticket);

		$actions_collection = new \Application\DeskPRO\Tickets\TicketActions\ActionsCollection();
		foreach ($trigger->actions as $action_info) {
			$action = $factory->createFromInfo($action_info);
			if ($action) {
				if ($action instanceof \Application\DeskPRO\Tickets\TicketActions\ExecutionContextAware) {
					$action->setExecutionContext('trigger');
				}

				$actions_collection->add($action, array(
					'trigger' => $trigger,
					'sla' => $this->sla,
					'sla_status' => $status,
					'original_status' => $this->getOriginalStatus()
				));
			}
		}

		try {
			$actions_collection->apply($this->ticket->getTicketLogger(), $this->ticket, null);
		} catch (\Exception $e) {
			// Log the error but continue with execution
			$einfo = \DeskPRO\Kernel\KernelErrorHandler::getExceptionInfo($e);
			\DeskPRO\Kernel\KernelErrorHandler::logErrorInfo($einfo);
		}
	}

	public function _sendClientMessages($removed = false)
	{
		$person_id = 0;
		try {
			if (defined('DP_INTERFACE') && in_array(DP_INTERFACE, array('admin', 'agent', 'user'))) {
				if (App::has('session') && App::get('session')->getEntity()->person) {
					$person_id = App::get('session')->getEntity()->person->getId();
				}
			}
		} catch (\Exception $e) {}

		App::getDb()->insert('client_messages', array(
			'channel' => 'agent.ticket-sla-updated',
			'auth' => \Orb\Util\Strings::random(15, \Orb\Util\Strings::CHARS_KEY),
			'date_created' => date('Y-m-d H:i:s'),
			'data' => serialize(array(
				'ticket_id'      => $this->ticket->getId(),
				'ticket_agent_id' => $this->ticket->agent ? $this->ticket->agent->id : null,
				'ticket_agent_team_id' => $this->ticket->agent_team ? $this->ticket->agent_team->id : null,
				'sla_id'         => $this->sla->id,
				'sla_status'     => $this->sla_status,
				'original_status' => $this->getOriginalStatus(),
				'warn_date'      => $this->warn_date ? $this->warn_date->format('c') : null,
				'fail_date'      => $this->fail_date ? $this->fail_date->format('c') : null,
				'is_completed'   => $this->is_completed,
				'original_is_completed' => $this->getOriginalIsCompleted(),
				'removed'        => $removed,
				'via_person'     => $person_id
			))
		));
	}

public function calculateSlaTimeUntil($end_ts, Ticket $ticket)
	{
		if ($this->sla_type == self::TYPE_WAITING_TIME) {
			$time = 0;
			$work_hours_set = $this->getWorkHoursSet();
			foreach ($ticket->waiting_times AS $waiting) {
				if ($waiting['type'] == 'user' && $waiting['start'] < $end_ts) {
					$time += $work_hours_set->getWorkTimeBetween($waiting['start'], min($end_ts, $waiting['end']));
				}
			}

			return $time;
		} else {
			return $this->getWorkHoursSet()->getWorkTimeBetween($ticket->date_created, $end_ts);
		}
	}
 */