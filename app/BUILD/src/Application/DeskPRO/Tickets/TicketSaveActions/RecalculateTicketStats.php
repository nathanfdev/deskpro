<?php

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Class RecalculateTicketStats.
 */
class RecalculateTicketStats implements TicketSaveActionInterface
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * Constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($context->getEventType() == 'noop') {
            return;
        }

        $state = $ticket->getStateChangeRecorder();

        if ($state->isNewTicket() || $state->hasChangedField('message')) {
            $agentIds = $this->db->fetchAllCol('SELECT id FROM people WHERE is_agent = 1 AND is_deleted = 0 AND is_disabled = 0');
            if (!count($agentIds)) {
                return;
            }

            $ticket->count_agent_replies = $this->db->fetchColumn('
                SELECT COUNT(*)
                FROM tickets_messages
                WHERE ticket_id = ? AND is_agent_note = 0 AND person_id IN (?)
            ', [$ticket->getId(), $agentIds], 0, [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);

            $ticket->count_user_replies = $this->db->fetchColumn('
                SELECT COUNT(*)
                FROM tickets_messages
                WHERE ticket_id = ? AND person_id NOT IN (?)
            ', [$ticket->getId(), $agentIds], 0, [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);
        }
    }
}
