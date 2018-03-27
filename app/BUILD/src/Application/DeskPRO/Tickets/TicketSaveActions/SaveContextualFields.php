<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Service\CustomFieldManager;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class SaveContextualFields implements TicketSaveActionInterface
{
    private $manager;

    public function __construct(CustomFieldManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->manager->flush2($ticket, $ticket->person);
    }
}
