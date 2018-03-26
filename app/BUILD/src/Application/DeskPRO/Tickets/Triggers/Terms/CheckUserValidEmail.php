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
 * Checks if a user is validated.
 */
class CheckUserValidEmail extends AbstractTriggerTerm
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
        $person = $ticket->person;
        $email  = $ticket->person_email ?: $person->primary_email;

        if (!$email) {
            $context->getLogger()->debug('[CheckUserValidEmail] No email to check');

            return false;
        }

        $is_valid = $email->is_validated;

        $context->getLogger()->debug(sprintf('[CheckUserValidEmail] Email %s is %s', $email->email, $email->is_validated ? 'validated' : 'not valiadated'));

        if ($is_valid) {
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
