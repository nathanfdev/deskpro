<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks if SLAs on the ticket match a status.
 * Note that only SLAs that actually exist on the ticket contrinute to the result
 * of this check.
 *
 * @option int[]  sla_ids
 * @option string sla_status
 * @option bool   is_complete
 */
class CheckSlaStatus extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('sla_ids');
        $options->addValidNames('sla_status', 'is_complete');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();

        $map = [];
        foreach ($ticket->ticket_slas as $ticket_sla) {
            $map[$ticket_sla->sla->id] = $ticket_sla;
        }

        $check_status   = $options->get('sla_status');
        $check_complete = $options->get('is_complete');

        $has_any          = false;
        $any_complete     = false;
        $any_match_status = false;
        foreach ($options->get('sla_ids') as $sla_id) {
            if (!isset($map[$sla_id])) {
                continue;
            }

            $ticket_sla = $map[$sla_id];
            $has_any    = true;

            if ($check_status && $ticket_sla->sla_status == $check_status) {
                $any_match_status = true;
            }
            if ($ticket_sla->is_completed) {
                $any_complete = true;
            }
        }

        switch ($this->getTermOperator()) {
            case 'is':
            case 'contains':
                if ($has_any) {
                    if ($check_complete && !$any_complete) {
                        return false;
                    }
                    if ($check_status && !$any_match_status) {
                        return false;
                    }

                    return true;
                } else {
                    return false;
                }
                break;

            case 'not':
            case 'notcontains':
                return !$has_any;
                break;
        }

        return false;
    }
}
