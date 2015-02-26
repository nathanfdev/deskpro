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
 * @subpackage Tickets
 */

namespace Application\DeskPRO\Tickets\Slas;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketSla;
use Application\DeskPRO\People\PersonContextInterface;
use Orb\Util\Strings;

class SlaClientMessageSender implements PersonContextInterface
{
    const CHANNEL = 'agent.ticket-sla-updated';

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var array()
     */
    private $queue = array();


    /**
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }


    /**
     * @param Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person = $person;
    }


    /**
     * @param Ticket    $ticket         The ticket
     * @param TicketSla $ticket_sla     The SLA on the ticket
     * @param string    $orig_status    The original status before it was updated
     * @param string    $orig_completed The original is_completed state before it was updated
     */
    public function sendMessage(Ticket $ticket, TicketSla $ticket_sla, $orig_status, $orig_completed)
    {
        $this->queue[] = array(
            'channel'       => self::CHANNEL,
            'auth'          => Strings::random(15, Strings::CHARS_KEY),
            'date_created'  => date('Y-m-d H:i:s'),
            'data'          => serialize(array(
                'ticket_id'             => $ticket->id,
                'ticket_agent_id'       => $ticket->agent ? $ticket->agent->id : null,
                'ticket_Agent_team_id'  => $ticket->agent_team ? $ticket->agent_team->id : null,
                'sla_id'                => $ticket_sla->sla->id,
                'sla_status'            => $ticket_sla->sla_status,
                'original_status'       => $orig_status,
                'warn_date'             => $ticket_sla->warn_date ? $ticket_sla->warn_date->format('c') : null,
                'fail_date'             => $ticket_sla->fail_date ? $ticket_sla->fail_date->format('c') : null,
                'is_completed'          => $ticket_sla->is_completed,
                'original_is_completed' => $orig_completed,
                'removed'               => $ticket->hasSla($ticket_sla->sla) ? true : false,
                'via_person'            => $this->person ? $this->person->id : null
            ))
        );
    }


    /**
     * Send all messages.
     *
     * @return int How many messages were sent
     */
    public function sendQueue()
    {
        if (!$this->queue) {
            return 0;
        }

        $q = $this->queue;
        $this->queue = array();

        $this->db->batchInsert('client_messages', $q);

        return count($q);
    }
}
