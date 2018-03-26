<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\ORM\StateChange\ChangeSimple;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks if user submits feedback with specific rating.
 */
class CheckSatisfactionSubmittedRating extends AbstractTriggerTerm
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
        $state  = $ticket->getStateChangeRecorder();
        $rating = (int) $this->getTermOptions()->get('rating');

        foreach ($state->getChangesForField('feedback_rating') as $change) {
            /** @var ChangeSimple $change */
            if ((int) $change->getNew() === $rating) {
                return true;
            }
        }

        return false;
    }
}
