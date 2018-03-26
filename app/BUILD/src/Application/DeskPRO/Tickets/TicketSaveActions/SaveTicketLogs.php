<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator;
use Doctrine\ORM\EntityManager;

class SaveTicketLogs implements TicketSaveActionInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        $ticketlog_generator = new TicketLogGenerator($ticket, $context);
        $logs                = $ticketlog_generator->getLogEntries();

        foreach ($logs as $l) {
            $this->em->persist($l);
        }

        $context->getVars()->set('ticket_logs', $logs);
    }
}
