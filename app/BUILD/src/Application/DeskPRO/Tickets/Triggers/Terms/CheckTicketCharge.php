<?php

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks added billing charge.
 */
class CheckTicketCharge extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('amount');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();
        $state   = $ticket->getStateChangeRecorder();

        if (!$state->hasChangedField('charges')) {
            if ($this->getTermOperator() == 'not_isset') {
                return true;
            }

            return false;
        }
        if ($this->getTermOperator() == 'isset') {
            return true;
        }

        $amounts = [];
        foreach ($state->getNewTicketCharges() as $charge) {
            $amounts[] = $charge->charge_time ?: $charge->amount;
        }

        $value = TermValue::createWithValue($amounts);

        return $this->isStringMatch($ticket, $context, $value, $options['amount']);
    }
}
