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
 * Checks urgency.
 *
 * @option int urgency1
 * @option int urgency2
 */
class CheckUrgency extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('urgency1');
        $options->addValidNames('urgency1', 'urgency2');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();

        if ($this->getTermOperator() == 'between' || $this->getTermOperator() == 'notbetween') {
            return $this->isIntRangeMatch($ticket, $context, 'urgency', $options['urgency1'], $options['urgency2']);
        } else {
            return $this->isIntMatch($ticket, $context, 'urgency', $options['urgency1']);
        }
    }
}
