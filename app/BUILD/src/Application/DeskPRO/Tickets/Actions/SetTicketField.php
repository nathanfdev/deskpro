<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class SetTicketField extends AbstractSetCustomField
{
    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return \Application\DeskPRO\CustomFields\FieldManager
     */
    public function getFieldManager(Ticket $ticket, ExecutorContextInterface $context)
    {
        return $this->getContainer()->getTicketFieldManager();
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return mixed
     */
    public function getApplicableObject(Ticket $ticket, ExecutorContextInterface $context)
    {
        return $ticket;
    }
}
