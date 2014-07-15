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

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\EntityRepository\TicketTrigger as TicketTriggerRepository;
use Application\DeskPRO\Tickets\Actions\ActionApplicatorInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Kernel\KernelErrorHandler;

class ExecTriggers implements TicketSaveActionInterface, ErrorCheckedInterface
{
	/**
	 * @var TicketTriggerRepository
	 */
	private $trigger_repos;

	/**
	 * @var ActionApplicatorInterface
	 */
	private $action_applicator;


	/**
	 * @param TicketTriggerRepository   $trigger_repos
	 * @param ActionApplicatorInterface $action_applicator
	 */
	public function __construct(TicketTriggerRepository $trigger_repos, ActionApplicatorInterface $action_applicator)
	{
		$this->trigger_repos = $trigger_repos;
		$this->action_applicator = $action_applicator;
	}


	/**
	 * @param Ticket                   $ticket
	 * @param ExecutorContextInterface $context
	 * @throws \Psr\Log\InvalidArgumentException
	 */
	public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
	{
		if (isset($GLOBALS['DP_ESCALATION_RUNNING'])) {
			return;
		}
		if ($context->getEventType() == 'noop') {
			return;
		}

		#------------------------------
		# For newreply we need to check if
		# there were other non-reply actions
		# for the second trigger loop
		#------------------------------

		$has_nonreply_actions = false;
		if ($context->getEventType() == 'newreply') {
			$exclude_types = array(
				'message' => true,
				'messages' => true,
				'waiting_times' => true,
				'total_user_waiting' => true,
				'total_to_first_reply' => true,
				'locked_by_agent' => true,
				'count_agent_replies' => true,
				'count_user_replies' => true
			);
			foreach ($ticket->getStateChangeRecorder()->getChangedFields() as $f) {
				if (!isset($exclude_types[$f]) && strpos($f, 'date_') === false) {
					$context->getLogger()->info(sprintf("[ExecTriggers] Found non-reply update to activate update#run_newreply triggers: %s", $f));
					$has_nonreply_actions = true;
					break;
				}
			}
		}

		#------------------------------
		# Run through triggers
		#------------------------------

		$triggers = $this->trigger_repos->getTriggersForEventType($context->getEventType());

		$trigger_ids = array_map(function($t) { return $t->id; }, is_array($triggers) ? $triggers : $triggers->toArray());
		$context->getLogger()->info(sprintf("[ExecTriggers] Triggers for event %s: %s", $context->getEventType(), implode(', ', $trigger_ids)));

		$has_stop_signal = false;

		foreach ($triggers as $trigger) {
			if ($context->getVars()->has('stop_triggers')) {
				$context->getLogger()->info("[Triggers] Got stop signal");
				$has_stop_signal = true;
				break;
			}

			$this->runTrigger($trigger, $ticket, $context);
		}

		#------------------------------
		# More triggers
		# - If the event is newreply, then we need a second loop
		# to run through update triggers that have the run_newreply option enabled
		# (if we have other prop changes)
		#------------------------------

		if ($context->getEventType() == 'newreply' && $has_nonreply_actions && !$has_stop_signal) {
			$context->getLogger()->info("[Triggers] Running through update triggers that have run_newreply event flag");
			$alt_triggers = $this->trigger_repos->getTriggersForEventType('update');
			$alt_triggers = is_array($alt_triggers) ? $alt_triggers : $alt_triggers->toArray();

			$alt_triggers = array_filter($alt_triggers, function($t) { return $t->hasEventFlag(TicketTrigger::EVENT_FLAG_RUN_NEWREPLY); });
			$alt_trigger_ids = array_map(function($t) { return $t->id; }, $alt_triggers);

			$context->getLogger()->info(sprintf("[ExecTriggers] Triggers for event update#run_newreply: %s", 'update', implode(', ', $alt_trigger_ids)));

			foreach ($alt_triggers as $trigger) {
				if ($context->getVars()->has('stop_triggers')) {
					$context->getLogger()->info("[Triggers] Got stop signal");
					break;
				}

				$this->runTrigger($trigger, $ticket, $context);
			}
		}
	}


	/**
	 * @param TicketTrigger            $trigger
	 * @param Ticket                   $ticket
	 * @param ExecutorContextInterface $context
	 */
	private function runTrigger(TicketTrigger $trigger, Ticket $ticket, ExecutorContextInterface $context)
	{
		$state = $ticket->getStateChangeRecorder();
		$context->getVars()->set('trigger_id', $trigger['id']);

		$mode_var = null;
		switch ($context->getEventPerformer()) {
			case 'agent':
				$mode_var = $trigger->by_agent_mode;
				break;
			case 'user':
				$mode_var = $trigger->by_user_mode;
				break;
		}
		if ($mode_var) {
			$is_method_match = in_array($context->getEventMethod(), $mode_var);
		} else {
			$is_method_match = false;
		}
		if (!$is_method_match) {
			$context->getLogger()->info(sprintf("[ExecTriggers] Skip trigger #%s due to method mismatch: %s != (%s) %s", $trigger->id, $context->getEventMethod(), $context->getEventPerformer() ?: '', implode(', ', $mode_var ?: array('NONE'))));
			return;
		}

		$ts = microtime(true);

		$match = $trigger->terms->isTriggerMatch($ticket, $context);

		if ($match) {
			$state->setCurrentChangeMetadata(array('trigger' => $trigger));
			$context->getLogger()->info(sprintf("[ExecTriggers] ----- BEGIN TRIGGER #%s :: %s -----", $trigger->id, $trigger->title));

			try {
				$this->action_applicator->apply($trigger->actions, $ticket, $context);
			} catch (\Exception $e) {
				$context->getLogger()->error(sprintf("[ExecTriggers] Exception: [%s] %s", $e->getCode(), $e->getMessage()), array('exception' => $e));
				KernelErrorHandler::logException($e);
			}

			$context->getLogger()->info(sprintf("[ExecTriggers] ----- FINISH TRIGGER #%s :: %.4fs -----", $trigger->id, microtime(true)-$ts));
			$state->clearCurrentChangeMetaData();
		}
	}
}