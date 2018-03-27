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
 * Checks if the ticket has labels.
 *
 * @option string[] labels
 */
class CheckJIRANewLinkedIssue extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('project');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();
        if (!$change = $ticket->getStateChangeRecorder()->getCombinedChangeForField('jira.linked')) {
            return false;
        }
        $issue = $change->getData();

        if ($options['project'] && $options['project'] != $issue['fields']['project']['id']) {
            return false;
        }

        return true;
    }
}
