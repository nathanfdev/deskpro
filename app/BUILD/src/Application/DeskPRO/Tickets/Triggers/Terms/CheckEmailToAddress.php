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
 * Checks to addresses for an email.
 *
 * @option string email
 */
class CheckEmailToAddress extends AbstractTriggerTerm
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
        if (!$context->hasEmailContext()) {
            return false;
        }

        $reader  = $context->getEmailContext();
        $strings = [];
        foreach ($reader->getToAddresses() as $email) {
            $strings[] = $email->getEmail();
        }

        $options = $this->getTermOptions();
        $value   = TermValue::createWithValue($strings);

        return $this->isStringMatch($ticket, $context, $value, $options['email']);
    }
}
