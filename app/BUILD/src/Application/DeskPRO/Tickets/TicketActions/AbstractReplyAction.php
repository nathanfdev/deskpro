<?php

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonContextInterface;

/**
 * Class AbstractReplyAction.
 */
abstract class AbstractReplyAction extends AbstractAction implements PersonContextInterface, PermissionableAction
{
    const REPLY_POS_APPEND    = 'append';
    const REPLY_POS_PREPEND   = 'prepend';
    const REPLY_POS_OVERWRITE = 'overwrite';

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person_context;

    /**
     * @var string|null
     */
    protected $reply_pos;

    /**
     * @param Ticket $ticket
     *
     * @return Person|null
     */
    public function getTicketPerson(Ticket $ticket)
    {
        $person = $this->person_context;

        if (!$person) {
            if ($ticket->getAgent()) {
                $person = $ticket->getAgent();
            } else {
                // Try to find last agent to replied in ticket
                $agent_id = App::getDb()->fetchColumn('
                    SELECT tickets_messages.person_id
                    FROM tickets_messages
                    LEFT JOIN people ON (people.id = tickets_messages.person_id)
                    WHERE tickets_messages.ticket_id = 1 AND people.is_agent = 1
                    ORDER BY tickets_messages.id DESC
                ');

                if ($agent_id) {
                    $person = App::getDataService('Agent')->get($agent_id);
                }
            }
        }

        return $person;
    }

    /**
     * @return string
     */
    public function getReplyPos()
    {
        return $this->reply_pos;
    }

    /**
     * @param Ticket $ticket
     *
     * @return string
     */
    abstract public function getMessageContent(Ticket $ticket);
}
