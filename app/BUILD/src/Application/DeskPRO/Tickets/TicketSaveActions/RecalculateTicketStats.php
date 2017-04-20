<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
