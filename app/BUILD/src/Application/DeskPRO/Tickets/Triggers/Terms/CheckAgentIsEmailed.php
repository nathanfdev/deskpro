<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\ORM\StateChange\ChangeEmailLog;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks to see if any user emails have been sent yet.
 *
 * @option string template Optionally the name of a specific template you want to check
 */
class CheckAgentIsEmailed extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('template');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $template_name = $this->getTermOptions()->get('template', null);

        $state    = $ticket->getStateChangeRecorder();
        $did_send = false;

        foreach ($state->getChangesForField('ticket_email') as $log) {
            if (!($log instanceof ChangeEmailLog)) {
                continue;
            }

            if ($log->getUserMode() != 'agent') {
                continue;
            }

            if ($template_name) {
                if ($log->getTemplate() == $template_name) {
                    $did_send = true;
                    break;
                }
            } else {
                $did_send = true;
                break;
            }
        }

        $op = $this->getTermOperator();
        if ($did_send) {
            if ($op == 'is') {
                return true;
            } else {
                return false;
            }
        } else {
            if ($op == 'not') {
                return true;
            } else {
                return false;
            }
        }
    }
}
