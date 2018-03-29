<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketSla;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Completes or uncompletes an SLA.
 *
 * @option int[] sla_ids       SLAs to set complete on
 * @option bool  sla_status    'auto', 'nochange', 'ok', 'warning', 'fail'
 *                             auto: recalc now, nochange: do nothing, ok: set to ok, warning: set to warning, fail: set to failed
 */
class SetSlasComplete extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('sla_status', 'sla_ids');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$ticket->ticket_slas || !count($ticket->ticket_slas)) {
            return;
        }

        $sla_ids = $this->getActionOption('sla_ids');
        $sla_ids = array_combine($sla_ids, $sla_ids);

        $set_status = $this->getActionOption('sla_status');

        foreach ($ticket->ticket_slas as $ticket_sla) {
            if (!isset($sla_ids[-1]) && !isset($sla_ids[$ticket_sla->sla->id])) {
                continue;
            }

            $calc = $ticket_sla->sla->getCalculator();

            switch ($set_status) {
                case 'auto':
                    if ($set_warn_date = $calc->calculateWarnDate($ticket)) {
                        $ticket_sla->warn_date = $set_warn_date;
                    }
                    if ($set_fail_date = $calc->calculateFailDate($ticket)) {
                        $ticket_sla->fail_date = $set_fail_date;
                    }

                    if ($ticket_sla->sla_status == 'ok' || $ticket_sla->sla_status == 'warning') {
                        if ($calc->isTicketSlaFailed($ticket, $ticket_sla)) {
                            $ticket_sla->sla_status = TicketSla::STATUS_FAIL;
                        }
                    } elseif ($ticket_sla->sla_status == 'ok') {
                        if ($calc->isTicketSlaWarning($ticket, $ticket_sla)) {
                            $ticket_sla->sla_status = TicketSla::STATUS_WARNING;
                        }
                    } else {
                        $ticket_sla->sla_status = TicketSla::STATUS_OK;
                    }
                    break;
                case 'ok':
                    $ticket_sla->sla_status = TicketSla::STATUS_OK;
                    break;
                case 'warning':
                    $ticket_sla->sla_status = TicketSla::STATUS_WARNING;
                    break;
                case 'fail':
                    $ticket_sla->sla_status = TicketSla::STATUS_FAIL;
                    break;
            }

            $completed_date = $calc->calculateCompletedDate($ticket);
            $ticket_sla->setIsCompleted(true, $completed_date);
            $this->getContainer()->getEm()->persist($ticket_sla);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'slas')) {
            return ['slas'];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}
