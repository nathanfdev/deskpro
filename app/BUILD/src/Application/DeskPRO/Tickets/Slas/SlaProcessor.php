<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\Slas;

use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketSla;
use Application\DeskPRO\EntityRepository\TicketSla as TicketSlaRepository;
use Application\DeskPRO\ORM\StateChange\ChangeSimple;
use Application\DeskPRO\Tickets\Actions\ActionApplicator;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketManager;
use Doctrine\ORM\EntityManager;
use DpSys\LowError\SystemErrorHandler;

class SlaProcessor
{
    /**
     * @var \Doctrine\ORM\EntityManager
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

    /**
     * @var callable
     */
    private $limiterCallback;

    public function __construct(EntityManager $em, ActionApplicator $action_applicator, SlaClientMessageSender $cm_sender)
    {
        $this->em                = $em;
        $this->action_applicator = $action_applicator;
        $this->cm_sender         = $cm_sender;
    }

    /**
     * Callback called to check if we should stop processing SLAs (e.g. time limit, memory check, whatever).
     *
     * The callback recieves: ['type' => 'fail|warning', 'count' => $how_many_so_far]
     * The callback must return: True means to enact the limit (i.e. stop processing), any other value is ignored (processing continues)
     *
     * @param callable $cb
     */
    public function setLimiterCallback($cb)
    {
        $this->limiterCallback = $cb;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function calculateSlas(Ticket $ticket, ExecutorContextInterface $context)
    {
        $state = $ticket->getStateChangeRecorder();

        //------------------------------
        // Get what we should be doing
        //------------------------------

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

        //------------------------------
        // Perform calcs
        //------------------------------

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
                if ($ticket_sla->sla_status == 'warning') {
                    if ($calc->isTicketSlaFailed($ticket, $ticket_sla)) {
                        $ticket->getStateChangeRecorder()->recordChange(new ChangeSimple(
                            'ticket_sla_status',
                            ['ticket_sla' => $ticket_sla, 'sla' => $ticket_sla->sla, 'status' => $ticket_sla->sla_status],
                            ['ticket_sla' => $ticket_sla, 'sla' => $ticket_sla->sla, 'status' => 'fail']
                        ));
                        $context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- set failed', $ticket_sla->sla->id, $ticket_sla->sla->title));
                        $ticket_sla->sla_status = TicketSla::STATUS_FAIL;
                        $do_triggers            = true;
                    }
                } elseif ($ticket_sla->sla_status == 'ok') {
                    if ($calc->isTicketSlaWarning($ticket, $ticket_sla)) {
                        $ticket->getStateChangeRecorder()->recordChange(new ChangeSimple(
                            'ticket_sla_status',
                            ['ticket_sla' => $ticket_sla, 'sla' => $ticket_sla->sla, 'status' => $ticket_sla->sla_status],
                            ['ticket_sla' => $ticket_sla, 'sla' => $ticket_sla->sla, 'status' => 'warning']
                        ));
                        $context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- set warning', $ticket_sla->sla->id, $ticket_sla->sla->title));
                        $ticket_sla->sla_status = TicketSla::STATUS_WARNING;
                        $do_triggers            = true;
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
            $this->em->flush();

            if ($current_complete != $ticket_sla->is_completed) {
                $context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- is_complete: %s', $ticket_sla->sla->id, $ticket_sla->sla->title, $ticket_sla->is_completed ? 'true' : 'false'));
                $ticket->getStateChangeRecorder()->recordChange(new ChangeSimple(
                    'ticket_sla_complete',
                    ['ticket_sla' => $ticket_sla, 'sla' => $ticket_sla->sla, 'complete' => $current_complete],
                    ['ticket_sla' => $ticket_sla, 'sla' => $ticket_sla->sla, 'complete' => $ticket_sla->is_completed]
                ));
            }
            if ($current_status != $ticket_sla->sla_status) {
                $context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- sla_status: %s', $ticket_sla->sla->id, $ticket_sla->sla->title, $ticket_sla->sla_status));
            }

            if ($current_complete != $ticket_sla->is_completed || $current_status != $ticket_sla->sla_status) {
                $this->cm_sender->sendMessage($ticket, $ticket_sla, $current_status, $current_complete);
            }

            if ($do_triggers) {
                if ($current_status == TicketSla::STATUS_OK && in_array($ticket_sla->sla_status, [TicketSla::STATUS_WARNING, TicketSla::STATUS_FAIL])) {
                    $context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- Executing WARN actions', $ticket_sla->sla->id, $ticket_sla->sla->title));
                    $this->executeSlaActions($ticket, $ticket_sla->sla, TicketSla::STATUS_WARNING, $context);
                }
                if (in_array($current_status, [TicketSla::STATUS_OK, TicketSla::STATUS_WARNING]) && $ticket_sla->sla_status == TicketSla::STATUS_FAIL) {
                    $context->getLogger()->info(sprintf('[SlaProcessor] SLA#%d %s -- Executing FAIL actions', $ticket_sla->sla->id, $ticket_sla->sla->title));
                    $this->executeSlaActions($ticket, $ticket_sla->sla, TicketSla::STATUS_FAIL, $context);
                }
            }
        }
    }

    /**
     * Look up SLAs in the db that are past warning threshold and update them.
     *
     * @param callback      $context_factory A factory that returns a new ExecutorContext
     * @param TicketManager $tm
     *
     * @return int
     */
    public function processAllFailed($context_factory, TicketManager $tm)
    {
        $count = 0;

        /** @var TicketSlaRepository $slasRepository */
        $slasRepository = $this->em->getRepository(TicketSla::class);
        $ticket_slas    = $slasRepository->getTicketSlasPastThreshold('fail');
        foreach ($ticket_slas as $ticket_sla) {
            if ($this->limiterCallback) {
                if (call_user_func($this->limiterCallback, ['type' => 'fail', 'count' => $count]) === true) {
                    break;
                }
            }

            /* @var TicketSla $ticket_sla */

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
                $this->em->flush();

                $this->cm_sender->sendMessage($ticket_sla->ticket, $ticket_sla, $current_status, $current_complete);

                ++$count;

                $context = $context_factory($ticket_sla->ticket, $ticket_sla->sla, $ticket_sla, 'fail');
                if (!($context instanceof ExecutorContextInterface)) {
                    throw new \InvalidArgumentException('context_factory did not return ExecutorContextInterface');
                }

                $ticket_sla->ticket->disableAutoTicketProcess();
                $this->executeSlaActions($ticket_sla->ticket, $ticket_sla->sla, 'fail', $context);
                $tm->saveTicket($ticket_sla->ticket, $context);
            }
        }

        return $count;
    }

    /**
     * Look up SLAs in the db that are past failing threshold and update them.
     *
     * @param callback      $context_factory A factory that returns a new ExecutorContext
     * @param TicketManager $tm
     *
     * @return int
     */
    public function processAllWarning($context_factory, TicketManager $tm)
    {
        $count = 0;

        $ticket_slas = $this->em->getRepository('DeskPRO:TicketSla')->getTicketSlasPastThreshold('warning');
        foreach ($ticket_slas as $ticket_sla) {
            if ($this->limiterCallback) {
                if (call_user_func($this->limiterCallback, ['type' => 'warning', 'count' => $count]) === true) {
                    break;
                }
            }

            /* @var TicketSla $ticket_sla */

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
                $this->em->flush();

                ++$count;

                $this->cm_sender->sendMessage($ticket_sla->ticket, $ticket_sla, $current_status, $current_complete);

                $context = $context_factory($ticket_sla->ticket, $ticket_sla->sla, $ticket_sla, 'warning');
                if (!($context instanceof ExecutorContextInterface)) {
                    throw new \InvalidArgumentException('context_factory did not return ExecutorContextInterface');
                }

                $ticket_sla->ticket->disableAutoTicketProcess();
                $this->executeSlaActions($ticket_sla->ticket, $ticket_sla->sla, 'warning', $context);
                $tm->saveTicket($ticket_sla->ticket, $context);
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
        $state->setCurrentChangeMetadata(['sla' => $sla, 'sla_status' => $status]);
        $context->getLogger()->info(sprintf("[SlaProcessor] ----- BEGIN SLA.$status #%s :: %s >> Ticket %d -----", $sla->id, $sla->title, $ticket->id));

        try {
            if ($status == TicketSla::STATUS_WARNING) {
                $actions = $sla->warn_actions;
            } else {
                $actions = $sla->fail_actions;
            }

            $this->action_applicator->apply($actions, $ticket, $context);
        } catch (\Exception $e) {
            $context->getLogger()->error(sprintf('[SlaProcessor] Exception: [%s] %s', $e->getCode(), $e->getMessage()), ['exception' => $e]);
            SystemErrorHandler::logException($e);
        }

        $context->getLogger()->info(sprintf("[SlaProcessor] ----- FINISH SLA.$status #%s :: %.4fs -----", $sla->id, microtime(true) - $ts));
        $state->clearCurrentChangeMetaData();
    }
}
