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
 * Checks if the user was created during this request.
 */
class CheckUserIsNew extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $is_news = $ticket->person->isNewPerson();

        if ($is_news) {
            if ($this->getTermOperator() == 'is') {
                return true;
            } else {
                return false;
            }
        } else {
            if ($this->getTermOperator() == 'is') {
                return false;
            } else {
                return true;
            }
        }
    }
}
