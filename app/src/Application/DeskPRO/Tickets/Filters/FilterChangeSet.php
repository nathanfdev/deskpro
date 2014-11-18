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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\Entity\ClientMessage;
use Application\DeskPRO\Entity\Ticket;

class FilterChangeSet
{
    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    /**
     * @var int
     */
    private $state_id;

    /**
     * @var \Application\DeskPRO\Entity\TicketFilter[]
     */
    private $affected_filters = array();

    /**
     * @var FilterChange[]
     */
    private $changed_filters  = array();

    public function __construct(Ticket $ticket, $state_id, array $affected_filters, array $changed_filters)
    {
        $this->ticket           = $ticket;
        $this->state_id         = $state_id;
        $this->affected_filters = $affected_filters;
        $this->changed_filters  = $changed_filters;
    }


    /**
     * @return \Application\DeskPRO\Entity\TicketFilter[]
     */
    public function getAffectedFilters()
    {
        return $this->affected_filters;
    }


    /**
     * @return \Application\DeskPRO\Tickets\Filters\FilterChange[]
     */
    public function getChangedFilters()
    {
        return $this->changed_filters;
    }


    /**
     * @return int
     */
    public function getStateId()
    {
        return $this->state_id;
    }


    /**
     * @return \Application\DeskPRO\Entity\Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }


    /**
     * Get an array of client messages to send to clients about lists updating.
     *
     * @return \Application\DeskPRO\Entity\ClientMessage[]
     */
    public function getListUpdateClientMessages()
    {
        $messages = array();

        #------------------------------
        # CMs for filters
        #------------------------------

        foreach ($this->changed_filters as $filter_change) {
            $filter = $filter_change->getFilter();

            foreach ($filter_change->getAgentsAdded() as $agent) {
                $cm = new ClientMessage();
                $cm->channel = 'agent.filter-update';
                $cm->data = array(
                    'ticket_id' => $this->ticket->id,
                    'filter_id' => $filter->id,
                    'op'        => 'add',
                );
                $cm->for_person = $agent;
                $cm->created_by_client = 'sys';
                $messages[] = $cm;
            }
            foreach ($filter_change->getAgentsRemoved() as $agent) {
                $cm = new ClientMessage();
                $cm->channel = 'agent.filter-update';
                $cm->data = array(
                    'ticket_id' => $this->ticket->id,
                    'filter_id' => $filter->id,
                    'op'        => 'del',
                );
                $cm->for_person = $agent;
                $cm->created_by_client = 'sys';
                $messages[] = $cm;
            }
        }

        return $messages;
    }
}
