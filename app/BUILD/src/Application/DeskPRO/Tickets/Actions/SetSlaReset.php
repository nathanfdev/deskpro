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
 * Resets all SLAs. Uncompletes them, resets them to OK and recalcs dates.
 * Note that this might result in nothing happening depending on the type of SLA.
 * For example, a 'first repsonse' SLA being reset will calculate to the exact same results because
 * first response date is fixed. However, a 'time until resolution' might change if the ticket was re-opened
 * during the life of the ticket.
 *
 * @option int[] sla_ids       SLAs to reset
 */
class SetSlaReset extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('sla_ids');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $sla_ids = $this->getActionOption('sla_ids');
        $sla_ids = array_combine($sla_ids, $sla_ids);

        foreach ($ticket->ticket_slas as $ticket_sla) {
            if (!isset($sla_ids[-1]) && !isset($sla_ids[$ticket_sla->sla->id])) {
                continue;
            }

            $calc = $ticket_sla->sla->getCalculator();

            $ticket_sla->warn_date  = $calc->calculateWarnDate($ticket);
            $ticket_sla->fail_date  = $calc->calculateFailDate($ticket);
            $ticket_sla->sla_status = TicketSla::STATUS_OK;
            if ($ticket_sla->sla_status == 'ok' || $ticket_sla->sla_status == 'warning') {
                if ($calc->isTicketSlaFailed($ticket, $ticket_sla)) {
                    $ticket_sla->sla_status = TicketSla::STATUS_FAIL;
                }
            } elseif ($ticket_sla->sla_status == 'ok') {
                if ($calc->isTicketSlaWarning($ticket, $ticket_sla)) {
                    $ticket_sla->sla_status = TicketSla::STATUS_WARNING;
                }
            }

            $completed_date = $calc->calculateCompletedDate($ticket);
            if ($completed_date) {
                $ticket_sla->setIsCompleted(true, $completed_date);
            } else {
                $ticket_sla->setIsCompleted(false, $completed_date);
            }
            $this->getContainer()->getEm()->persist($ticket_sla);
            $this->getContainer()->getEm()->flush($ticket_sla);
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
