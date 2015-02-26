<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\DeskPRO\Tickets\Escalations;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketEscalation;
use Application\DeskPRO\Monolog\NullLogger;
use Application\DeskPRO\Tickets\Actions\ActionApplicatorInterface;
use Application\DeskPRO\Tickets\TicketManager;
use Monolog\Logger;

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

    public function __construct(Connection $db, TicketManager $ticket_manager, ActionApplicatorInterface $action_applicator)
    {
        $this->db = $db;
        $this->ticket_manager = $ticket_manager;
        $this->logger = new NullLogger();
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
        $this->db->insert('ticket_escalation_logs', array(
            'ticket_id'     => $ticket->id,
            'escalation_id' => $esc->id,
            'date_ran'      => date('Y-m-d H:i:s'),
            'date_criteria' => $d->format('Y-m-d H:i:s')
        ));

        $this->ticket_manager->markAsManaged($ticket);

        $context = $this->ticket_manager->createSystemExecutorContext();
        $state = $ticket->getStateChangeRecorder();
        $state->setCurrentChangeMetadata(array('escalation' => $esc));

        $this->action_applicator->apply($esc->actions, $ticket, $context);
        $this->ticket_manager->saveTicket($ticket, $context);
    }
}
