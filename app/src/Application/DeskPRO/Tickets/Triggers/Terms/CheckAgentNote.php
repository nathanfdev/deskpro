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
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks the agent-added note
 *
 * @option string message
 * @option bool disable_full Do not check the full, raw message, only the cleaned cut one
 */
class CheckAgentNote extends AbstractTriggerTerm
{
    /**
     * {@inheritDoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('message');

        return $options;
    }


    /**
     * {@inheritDoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();
        $state = $ticket->getStateChangeRecorder();

        if (!$state->hasNewAgentNote()) {
            if ($this->getTermOperator() == 'not_isset') {
                return true;
            }

            return false;
        }
        if ($this->getTermOperator() == 'isset') {
            return true;
        }

        $no_full = $options->get('disable_full');

        $strings = array();

        foreach ($state->getNewAgentNotes() as $reply) {
            $strings[] = $reply->message;
            $strings[] = trim(preg_replace('#\s+#' , ' ', strip_tags($reply->message)));

            if (!$no_full && $reply->message_raw) {
                $strings[] = $reply->message_raw;
                $strings[] = trim(preg_replace('#\s+#' , ' ', strip_tags($reply->message_raw)));
            }
        }

        $value = TermValue::createWithValue($strings);

        return $this->isStringMatch($ticket, $context, $value, $options['message']);
    }
}
