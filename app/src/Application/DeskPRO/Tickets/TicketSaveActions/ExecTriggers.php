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
use Application\DeskPRO\EntityRepository\TicketTrigger as TicketTriggerRepository;
use Application\DeskPRO\Tickets\Actions\ActionApplicatorInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Kernel\KernelErrorHandler;

class ExecTriggers implements TicketSaveActionInterface
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

		$state = $ticket->getStateChangeRecorder();

		$triggers = $this->trigger_repos->getTriggersForEventType($context->getEventType());

		$trigger_ids = array_map(function($t) { return $t->id; }, is_array($triggers) ? $triggers : $triggers->toArray());
		$context->getLogger()->info(sprintf("[ExecTriggers] Triggers for event %s: %s", $context->getEventType(), implode(', ', $trigger_ids)));

		/** @var \Application\DeskPRO\Entity\TicketTrigger[] $triggers */
		foreach ($triggers as $trigger) {
			if ($context->getVars()->has('stop_triggers')) {
				$context->getLogger()->info("[Triggers] Got stop signal");
				break;
			}

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
				continue;
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
}