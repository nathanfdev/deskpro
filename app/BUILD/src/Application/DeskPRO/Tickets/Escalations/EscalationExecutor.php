<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Escalations;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketEscalation;
use Application\DeskPRO\Monolog\NullLogger;
use Application\DeskPRO\Tickets\Actions\ActionApplicatorInterface;
use Application\DeskPRO\Tickets\TicketManager;
use Monolog\Logger;

/**
 * Class EscalationExecutor.
 */
class EscalationExecutor
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var TicketManager
     */
    private $ticket_manager;

    /**
     * @var ActionApplicatorInterface
     */
    private $action_applicator;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param Connection                $db
     * @param TicketManager             $ticket_manager
     * @param ActionApplicatorInterface $action_applicator
     */
    public function __construct(Connection $db, TicketManager $ticket_manager, ActionApplicatorInterface $action_applicator)
    {
        $this->db                = $db;
        $this->ticket_manager    = $ticket_manager;
        $this->logger            = new NullLogger();
        $this->action_applicator = $action_applicator;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param TicketEscalation $esc
     * @param Ticket           $ticket
     */
    public function applyEscalation(TicketEscalation $esc, Ticket $ticket)
    {
        $this->_doApplyEscalation($esc, $ticket);
    }

    /**
     * @param TicketEscalation $esc
     * @param Ticket           $ticket
     */
    private function _doApplyEscalation(TicketEscalation $esc, Ticket $ticket)
    {
        // Save log
        $d = $ticket->get($esc->getTicketTimeField()) ?: new \DateTime();
        $this->db->insert('ticket_escalation_logs', [
            'ticket_id'     => $ticket->id,
            'escalation_id' => $esc->id,
            'date_ran'      => date('Y-m-d H:i:s'),
            'date_criteria' => $d->format('Y-m-d H:i:s'),
        ]);

        $this->ticket_manager->markAsManaged($ticket);

        $context = $this->ticket_manager->createSystemExecutorContext();
        $state   = $ticket->getStateChangeRecorder();
        $state->setCurrentChangeMetadata(['escalation' => $esc]);

        $this->action_applicator->apply($esc->actions, $ticket, $context);
        $this->ticket_manager->saveTicket($ticket, $context);
    }
}
