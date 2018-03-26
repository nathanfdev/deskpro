<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Checks if the user is a manager of their org.
 */
class CheckUserOrgManager extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $person = $ticket->person;
        $op     = $this->getTermOperator();

        if (!$person->organization) {
            $is_manager = false;
        } else {
            $is_manager = (bool) $person->organization_manager;
        }

        if ($is_manager) {
            if ($op == 'is') {
                return true;
            } else {
                return false;
            }
        } else {
            if ($op == 'is') {
                return false;
            } else {
                return true;
            }
        }
    }
}
