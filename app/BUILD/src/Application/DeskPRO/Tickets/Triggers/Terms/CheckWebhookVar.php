<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars\ExecutorContextVars;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks the value of a user var.
 *
 * @option string name  The name of the user var
 * @option string value Value to check for (not used for isset/notisset)
 */
class CheckWebhookVar extends AbstractTriggerTerm
{
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('name');
        $options->addValidNames('value');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();
        $name = $options->get('name');
        if (!$name) {
            return false;
        }

        $valueExists = ExecutorContextVars::exists($context, $name);
        if ($this->getTermOperator() == 'not_isset') {
            return !$valueExists;
        } else if ($this->getTermOperator() == 'isset') {
            return $valueExists;
        } else if (!$valueExists) {
            return false;
        }

        $value = ExecutorContextVars::get($context, $name);
        return $this->isStringMatch($ticket, $context, TermValue::createWithValue($value), $options['value']);
    }
}
