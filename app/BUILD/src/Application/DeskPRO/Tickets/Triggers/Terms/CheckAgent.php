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
 * Checks current agent.
 *
 * @option int[] agent_ids
 */
class CheckAgent extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('agent_ids');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();

        $agent_ids = $options['agent_ids'];
        if ($agent_ids && $context->getPersonContext() && in_array(-1, $agent_ids)) {
            $person = $context->getPersonContext();
            if ($person->is_agent) {
                $agent_ids[] = $person;
            }
        }

        return $this->isEntityMatch($ticket, $context, 'agent', 'id', $agent_ids);
    }
}
