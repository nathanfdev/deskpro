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
 * Checks the agent-added message.
 *
 * @option string message
 * @option bool disable_full Do not check the full, raw message, only the cleaned cut one
 */
class CheckAgentMessage extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('message');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();
        $state   = $ticket->getStateChangeRecorder();

        if (!$state->hasNewAgentReply()) {
            if ($this->getTermOperator() == 'not_isset') {
                return true;
            }

            return false;
        }
        if ($this->getTermOperator() == 'isset') {
            return true;
        }

        $no_full = $options->get('disable_full');

        $strings = [];

        foreach ($state->getNewAgentReplies() as $reply) {
            $strings[] = $reply->message;
            $strings[] = trim(preg_replace('#\s+#', ' ', strip_tags($reply->message)));

            if (!$no_full && $reply->message_raw) {
                $strings[] = $reply->message_raw;
                $strings[] = trim(preg_replace('#\s+#', ' ', strip_tags($reply->message_raw)));
            }
        }

        $value = TermValue::createWithValue($strings);

        return $this->isStringMatch($ticket, $context, $value, $options['message']);
    }
}
