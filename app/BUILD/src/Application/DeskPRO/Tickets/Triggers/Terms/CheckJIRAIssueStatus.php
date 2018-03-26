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
 * Checks issue(s) status(es).
 *
 * @option string[] labels
 */
class CheckJIRAIssueStatus extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('status', 'all');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();
        $op      = $this->getTermOperator();

        $all    = (bool) $options->get('all');
        $status = $options->get('status');

        $changeData = [];
        if ($change = $ticket->getStateChangeRecorder()->getCombinedChangeForField('jira.status')) {
            $changeData = $change->getData();
        }

        $match = false;
        foreach ($ticket->jira_issues as $issue) {
            switch ($op) {
                case 'changed':
                    $match = (bool) $changeData;
                    break;
                case 'changed_to':
                    $match = isset($changeData['to']) && $changeData['to'] == $status;
                    break;
                case 'changed_from':
                    $match = isset($changeData['from']) && $changeData['from'] == $status;
                    break;
                case 'is':
                    $match = $issue['status_id'] == $status;
                    break;
                case 'not':
                    $match = $issue['status_id'] != $status;
                    break;
            }

            // break on first false if all
            if ($all && !$match) {
                break;
            }
            // break on first true if any
            if (!$all && $match) {
                break;
            }
        }

        return $match;
    }
}
