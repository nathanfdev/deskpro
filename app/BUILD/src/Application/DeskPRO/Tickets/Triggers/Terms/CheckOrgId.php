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
 * Checks if the org ID matches.
 *
 * @option int id
 */
class CheckOrgId extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('id');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();

        if ($this->getTermOperator() == 'isset') {
            if ($ticket->organization) {
                return true;
            } else {
                return false;
            }
        } elseif ($this->getTermOperator() == 'not_isset') {
            if (!$ticket->organization) {
                return true;
            } else {
                return false;
            }
        }

        return $this->isEntityMatch($ticket, $context, 'organization', 'id', $options['id']);
    }
}
