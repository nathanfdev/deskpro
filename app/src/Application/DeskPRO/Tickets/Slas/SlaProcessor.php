<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\DeskPRO\Tickets\Slas;

use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityManager;
use Application\DeskPRO\Entity\TicketSla;
use Application\DeskPRO\Tickets\Actions\ActionApplicator;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Kernel\KernelErrorHandler;

class SlaProcessor
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\Tickets\Actions\ActionApplicator
	 */
	private $action_applicator;

	/**
	 * @var SlaClientMessageSender
	 */
	private $cm_sender;

	public function __construct(EntityManager $em, ActionApplicator $action_applicator, SlaClientMessageSender $cm_sender)
	{
		$this->em = $em;
		$this->action_applicator = $action_applicator;
		$this->cm_sender = $cm_sender;
	}


	/**
	 * @param Ticket                   $ticket
	 * @param ExecutorContextInterface $context
	 */
	public function calculateSlas(Ticket $ticket, ExecutorContextInterface $context)
	{
		$state = $ticket->getStateChangeRecorder();

		#------------------------------
		# Get what we should be doing
		#------------------------------

		$recalc = false;

		if (($state->isNewTicket() && !$ticket->hidden_status)) {
			$recalc = true;
		}
		if ($state->hasChangedField('status') || $state->hasChangedField('ticket_slas')) {
			$recalc = true;
		}
		if ($state->hasNewReply()) {
			$recalc = true;
		}

		if (!$recalc) {
			$context->getLogger()->info('[SlaProcessor] No ops');
			return;
		}

		#------------------------------
		# Perform calcs
		#------------------------------

		foreach ($ticket->ticket_slas as $ticket_sla) {
			// Dont touch ones that have been specifically set
			if ($ticket_sla->is_completed_set) {
				continue;
			}

			$current_complete = $ticket_sla->is_completed;
			$current_status   = $ticket_sla->sla_status;
			$do_triggers      = false;

			$calc = $ticket_sla->sla->getCalculator();

			$set_warn_date = $calc->calculateWarnDate($ticket) ?: null;
			if ($ticket_sla->warn_date != $set_warn_date) {
				$ticket_sla->warn_date = $set_warn_date;
				$context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- warn_date: %s', $ticket_sla->sla->id, $ticket_sla->sla->title, $set_warn_date ? $set_warn_date->format('Y-m-d H:i:s') : 'null'));
			}

			$set_fail_date = $calc->calculateFailDate($ticket) ?: null;
			if ($ticket_sla->fail_date != $set_fail_date) {
				$ticket_sla->fail_date = $set_fail_date;
				$context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- fail_date: %s', $ticket_sla->sla->id, $ticket_sla->sla->title, $set_fail_date ? $set_fail_date->format('Y-m-d H:i:s') : 'null'));
			}

			if (!$ticket_sla->is_completed) {
				if ($ticket_sla->sla_status == 'ok' || $ticket_sla->sla_status == 'warning') {
					if ($calc->isTicketSlaFailed($ticket, $ticket_sla)) {
						$context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- set failed', $ticket_sla->sla->id, $ticket_sla->sla->title));
						$ticket_sla->sla_status = TicketSla::STATUS_FAIL;
						$do_triggers = true;
					}
				} else if ($ticket_sla->sla_status == 'ok') {
					if ($calc->isTicketSlaWarning($ticket, $ticket_sla)) {
						$context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- set warning', $ticket_sla->sla->id, $ticket_sla->sla->title));
						$ticket_sla->sla_status = TicketSla::STATUS_WARNING;
						$do_triggers = true;
					}
				}
			}

			$completed_date = $calc->calculateCompletedDate($ticket);
			if ($completed_date) {
				$ticket_sla->setIsCompleted(true, $completed_date);
			} else {
				$ticket_sla->setIsCompleted(false, $completed_date);
			}

			$this->em->persist($ticket_sla);
			$this->em->flush($ticket_sla);

			if ($current_complete != $ticket_sla->is_completed) {
				$context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- is_complete: %s', $ticket_sla->sla->id, $ticket_sla->sla->title, $ticket_sla->is_completed ? 'true' : 'false'));
			}
			if ($current_status != $ticket_sla->sla_status) {
				$context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- sla_status: %s', $ticket_sla->sla->id, $ticket_sla->sla->title, $ticket_sla->sla_status));
			}

			if ($current_complete != $ticket_sla->is_completed || $current_status != $ticket_sla->sla_status) {
				$this->cm_sender->sendMessage($ticket, $ticket_sla, $current_status, $current_complete);
			}

			if ($do_triggers) {
				if ($current_status == TicketSla::STATUS_OK && in_array($ticket_sla->sla_status, array(TicketSla::STATUS_WARNING, TicketSla::STATUS_FAIL))) {
					$context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- Executing WARN actions', $ticket_sla->sla->id, $ticket_sla->sla->title));
					$this->executeSlaActions($ticket, $ticket_sla->sla, TicketSla::STATUS_WARNING, $context);
				}
				if (in_array($current_status, array(TicketSla::STATUS_OK, TicketSla::STATUS_WARNING)) && $ticket_sla->sla_status == TicketSla::STATUS_FAIL) {
					$context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- Executing FAIL actions', $ticket_sla->sla->id, $ticket_sla->sla->title));
					$this->executeSlaActions($ticket, $ticket_sla->sla, TicketSla::STATUS_FAIL, $context);
				}
			}
		}

		$this->cm_sender->sendQueue();
	}


	/**
	 * Look up SLAs in the db that are past warning threshold and update them.
	 *
	 * @param callback $context_factory A factory that returns a new ExecutorContext
	 * @return int
	 */
	public function processAllFailed($context_factory)
	{
		$count = 0;

		$ticket_slas = $this->em->getRepository('DeskPRO:TicketSla')->getTicketSlasPastThreshold('fail');
		foreach ($ticket_slas as $ticket_sla) {
			/** @var TicketSla $ticket_sla */

			// Already complete or not proper status (must currently be ok/warning aka not failed)
			if ($ticket_sla->is_completed && ($ticket_sla->sla_status == 'ok' || $ticket_sla->sla_status == 'warning')) {
				// completed
				continue;
			}

			$current_complete = $ticket_sla->is_completed;
			$current_status   = $ticket_sla->sla_status;

			if ($ticket_sla->sla->getCalculator()->isTicketSlaFailed($ticket_sla->ticket, $ticket_sla)) {

				$ticket_sla->sla_status = TicketSla::STATUS_FAIL;
				$this->em->persist($ticket_sla);
				$this->em->flush($ticket_sla);

				$this->cm_sender->sendMessage($ticket_sla->ticket, $ticket_sla, $current_status, $current_complete);
				$this->cm_sender->sendQueue();

				$count++;

				$context = $context_factory($ticket_sla->ticket, $ticket_sla->sla, $ticket_sla, 'fail');
				if (!($context instanceof ExecutorContextInterface)) {
					throw new \InvalidArgumentException("context_factory did not return ExecutorContextInterface");
				}

				$ticket_sla->ticket->disableAutoTicketProcess();
				$this->executeSlaActions($ticket_sla->ticket, $ticket_sla->sla, 'fail', $context);
			}
		}

		return $count;
	}


	/**
	 * Look up SLAs in the db that are past failing threshold and update them.
	 *
	 * @param callback $context_factory A factory that returns a new ExecutorContext
	 * @return int
	 */
	public function processAllWarning($context_factory)
	{
		$count = 0;

		$ticket_slas = $this->em->getRepository('DeskPRO:TicketSla')->getTicketSlasPastThreshold('warning');
		foreach ($ticket_slas as $ticket_sla) {
			/** @var TicketSla $ticket_sla */

			// Already complete or not proper status
			if ($ticket_sla->is_completed && ($ticket_sla->sla_status == 'ok')) {
				// completed
				continue;
			}

			$current_complete = $ticket_sla->is_completed;
			$current_status   = $ticket_sla->sla_status;

			if ($ticket_sla->sla->getCalculator()->isTicketSlaWarning($ticket_sla->ticket, $ticket_sla)) {

				$ticket_sla->sla_status = TicketSla::STATUS_WARNING;
				$this->em->persist($ticket_sla);
				$this->em->flush($ticket_sla);

				$count++;

				$this->cm_sender->sendMessage($ticket_sla->ticket, $ticket_sla, $current_status, $current_complete);
				$this->cm_sender->sendQueue();

				$context = $context_factory($ticket_sla->ticket, $ticket_sla->sla, $ticket_sla, 'warning');
				if (!($context instanceof ExecutorContextInterface)) {
					throw new \InvalidArgumentException("context_factory did not return ExecutorContextInterface");
				}

				$ticket_sla->ticket->disableAutoTicketProcess();
				$this->executeSlaActions($ticket_sla->ticket, $ticket_sla->sla, 'warning', $context);
			}
		}

		return $count;
	}


	/**
	 * @param Ticket                   $ticket
	 * @param Sla                      $sla
	 * @param                          $status
	 * @param ExecutorContextInterface $context
	 */
	private function executeSlaActions(Ticket $ticket, Sla $sla, $status, ExecutorContextInterface $context)
	{
		$ts = microtime(true);

		$state = $ticket->getStateChangeRecorder();
		$state->setCurrentChangeMetadata(array('sla' => $sla, 'sla_status' => $status));
		$context->getLogger()->info(sprintf("[SlaProcessor] ----- BEGIN SLA.$status #%s :: %s -----", $sla->id, $sla->title));

		try {
			if ($status == TicketSla::STATUS_WARNING) {
				$actions = $sla->warn_actions;
			} else {
				$actions = $sla->fail_actions;
			}

			$this->action_applicator->apply($actions, $ticket, $context);
		} catch (\Exception $e) {
			$context->getLogger()->error(sprintf("[SlaProcessor] Exception: [%s] %s", $e->getCode(), $e->getMessage()), array('exception' => $e));
			KernelErrorHandler::logException($e);
		}

		$context->getLogger()->info(sprintf("[SlaProcessor] ----- FINISH SLA.$status #%s :: %.4fs -----", $sla->id, microtime(true)-$ts));
		$state->clearCurrentChangeMetaData();
	}
}