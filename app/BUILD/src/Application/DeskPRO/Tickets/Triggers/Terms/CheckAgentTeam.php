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
 * Checks current agent team.
 *
 * @option int[] team_ids
 */
class CheckAgentTeam extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('team_ids');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();

        $team_ids = $options['team_ids'];
        if ($context->getPersonContext() && in_array(-1, $team_ids)) {
            $person = $context->getPersonContext();
            if ($person->is_agent) {
                $person->loadHelper('Agent');
                foreach ($person->getHelper('Agent')->getTeams() as $t) {
                    $team_ids[] = $t->id;
                }
            }
        }

        return $this->isEntityMatch($ticket, $context, 'agent_team', 'id', $team_ids);
    }
}
