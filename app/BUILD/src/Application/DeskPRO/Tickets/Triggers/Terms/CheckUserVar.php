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
 * Checks the value of a user var.
 *
 * @option string name  The name of the user var
 * @option string value Value to check for (not used for isset/notisset)
 */
class CheckUserVar extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
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

        if (!$context->getUserVars()->has($name)) {
            if ($this->getTermOperator() == 'not_isset') {
                return true;
            }

            return false;
        }
        if ($this->getTermOperator() == 'isset') {
            return true;
        }

        $value = TermValue::createWithValue($context->getUserVars()->get($name));

        return $this->isStringMatch($ticket, $context, $value, $options['value']);
    }
}
