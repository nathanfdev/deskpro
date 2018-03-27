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
 * Checks added message to see if it contains an attachment.
 *
 * @option string filename
 */
class CheckMessageAttach extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('filename');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();
        $state   = $ticket->getStateChangeRecorder();

        if (!$state->hasNewReply()) {
            if ($this->getTermOperator() == 'not_isset') {
                return true;
            }

            return false;
        }
        if ($this->getTermOperator() == 'isset') {
            return true;
        }

        $strings = [];
        foreach ($state->getNewAgentReplies() as $reply) {
            foreach ($reply->attachments as $attach) {
                $strings[] = $attach->blob->filename;
            }
        }

        $value = TermValue::createWithValue($strings);

        return $this->isStringMatch($ticket, $context, $value, $options['filename']);
    }
}
