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
 * Checks satisfaction.
 *
 * @option int rating
 */
class CheckSatisfaction extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('rating');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $op = $this->getTermOperator();

        if (!$ticket->date_feedback_rating) {
            if ($op == 'not_isset') {
                return true;
            }

            return false;
        }
        if ($op == 'isset') {
            return true;
        }

        $rating = (int) $this->getTermOptions()->get('rating', 0);
        if ($rating < 0) {
            $rating = -1;
        } elseif ($rating > 0) {
            $rating = 1;
        }

        return $this->isIntMatch($ticket, $context, 'feedback_rating', $rating);
    }
}
