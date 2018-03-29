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
 * Checks if SLAs are set on a ticket.
 *
 * @option int[]  sla_ids
 */
class CheckSla extends AbstractTriggerTerm
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
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();

        $map = [];
        foreach ($ticket->ticket_slas as $ticket_sla) {
            $map[$ticket_sla->sla->id] = $ticket_sla;
        }

        $passing = true;
        foreach ($options->get('sla_ids') as $sla_id) {
            if (!isset($map[$sla_id])) {
                $passing = false;
                break;
            }
        }

        switch ($this->getTermOperator()) {
            case 'is':
            case 'contains':
                return $passing;
                break;

            case 'not':
            case 'notcontains':
                return !$passing;
                break;
        }

        return false;
    }
}
