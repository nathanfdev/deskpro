<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Util as DeskPROUtil;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks if the ticket has labels.
 *
 * @option string[] labels
 */
class CheckLabel extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('labels');
        $options->addCallbackCheckedOption('labels', function ($v) {
            return !empty($v);
        });

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $labels = DeskPROUtil::labelsArrayFromString($this->getTermOptions()->get('labels', ''));

        if (!$labels) {
            return false;
        }

        return $this->isEntityMatch($ticket, $context, 'labels', 'label', $labels);
    }
}
