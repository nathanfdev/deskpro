<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\ORM\StateChange\ChangeEmailLog;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks to see if any user emails have been sent yet
 *
 * @option string template Optionally the name of a specific template you want to check
 */
class CheckUserIsEmailed extends AbstractTriggerTerm
{
    /**
     * {@inheritDoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('template');

        return $options;
    }


    /**
     * {@inheritDoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $template_name = $this->getTermOptions()->get('template', null);

        $state = $ticket->getStateChangeRecorder();
        $did_send = false;

        foreach ($state->getChangesForField('ticket_email') as $log) {
            if (!($log instanceof ChangeEmailLog)) {
                continue;
            }

            if ($log->getUserMode() != 'user') {
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
            if ($op == 'is') return true;
            else return false;
        } else {
            if ($op == 'not') return true;
            else return false;
        }
    }
}
