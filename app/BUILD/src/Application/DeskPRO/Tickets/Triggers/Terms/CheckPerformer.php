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
 * Checks if the current person is any of the specified.
 *
 * @option int[] person_ids
 */
class CheckPerformer extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('person_ids');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$context->getPersonContext()) {
            return false;
        }

        $options   = $this->getTermOptions();
        $performer = $context->getPersonContext();
        $check     = $options['person_ids'];

        // Assigned Agent
        if (in_array('-1', $check)) {
            return $performer->getId() === $ticket->getAgentId();
        }

        // Member of assigned team
        if (in_array('-2', $check) && ($team = $ticket->getAgentTeam())) {
            return $performer->getHelper('Agent')->isTeamMember($team->getId());
        }

        // Follower of the ticket
        if (in_array('-3', $check)) {
            return $ticket->hasParticipantPerson($performer->getId());
        }

        return $this->isIntMatch($ticket, $context, TermValue::createWithValue($performer->getId()), $check);
    }
}
