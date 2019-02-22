<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks status.
 *
 * @option string status
 */
class CheckStatus extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('status');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();
        $status  = $options['status'];
        // fallback to `hidden.delete`, `hidden.spam` statuses
        if (in_array($status, ['hidden.deleted', 'hidden.spam'])) {
            $status = App::getContainer()->getTicketStatuses()->findStatusOrException($status);
            $status = $status->getStatusCode();
        }

        return $this->isStringMatch($ticket, $context, 'status_code', $status);
    }
}
