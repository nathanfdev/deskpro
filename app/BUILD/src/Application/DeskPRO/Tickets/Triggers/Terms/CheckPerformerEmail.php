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
 * Checks the current user performers email address.
 *
 * @option string|string[] email
 */
class CheckPerformerEmail extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('email');

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

        $emails = [];
        foreach ($context->getPersonContext()->emails as $e) {
            $emails[] = $e->email;
        }

        $options = $this->getTermOptions();

        return $this->isStringMatch($ticket, $context, TermValue::createWithValue($emails), $options['email']);
    }
}
