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
