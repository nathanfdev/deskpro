<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Doctrine\DBAL\Driver\Connection;

class RecalculateTicketStats implements TicketSaveActionInterface
{
    /**
     * @var int[]
     */
    private $agent_ids = array();

    /**
     * @var Connection
     */
    private $db;


    /**
     * @param array      $agent_ids
     * @param Connection $db
     */
    public function __construct(array $agent_ids, Connection $db)
    {
        $this->agent_ids = $agent_ids;
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
            $agent_ids_in = implode(',', $this->agent_ids);

            $ticket->count_agent_replies = $this->db->fetchColumn("
                SELECT COUNT(*)
                FROM tickets_messages
                WHERE ticket_id = ? AND is_agent_note = 0 AND person_id IN ($agent_ids_in)
            ", array($ticket->id));

            $ticket->count_user_replies = $this->db->fetchColumn("
                SELECT COUNT(*)
                FROM tickets_messages
                WHERE ticket_id = ? AND person_id NOT IN ($agent_ids_in)
            ", array($ticket->id));
        }
    }

}
