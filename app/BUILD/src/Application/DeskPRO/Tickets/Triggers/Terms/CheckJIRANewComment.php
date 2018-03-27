<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\ORM\StateChange\ChangeData;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks if the ticket has labels.
 *
 * @option string[] labels
 */
class CheckJIRANewComment extends AbstractTriggerTerm
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
        $op      = $this->getTermOperator();
        /** @var ChangeData $change */
        $change = $state->getCombinedChangeForField('jira.comment');

        $data = $change ? $change->getData() : [];

        if (!$data) {
            return 'not_isset' === $op;
        }
        if ('isset' === $op) {
            return true;
        }
        $comment = $data['body'];

        $strings = [];

        $strings[] = $comment;
        $strings[] = trim(preg_replace('#\s+#', ' ', strip_tags($comment)));

        $value = TermValue::createWithValue($strings);

        return $this->isStringMatch($ticket, $context, $value, $options['message']);
    }
}
