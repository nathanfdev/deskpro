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

namespace DeskPRO\Bundle\AppBundle\Notification\Event\Ticket;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractSystemEvent;

/**
 * Class TicketFollowUpUpdatedEvent.
 */
class TicketFollowUpUpdatedEvent extends AbstractSystemEvent
{
    const EVENT_NAME = 'ticket.follow_up_updated';

    private $ticket   = null;
    private $ticketId = null;
    private $action   = null;

    /**
     * SnippetsUpdatedEvent constructor.
     *
     * @param Ticket $ticket
     * @param string $action
     */
    public function __construct($ticket, $action = null)
    {
        $this->ticket = $ticket;
        if ($ticket) {
            $this->ticketId = $ticket->getId();
        }
        $this->action = $action;
    }

    /**
     * @return Ticket|null
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @return string|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function __sleep()
    {
        return ['ticketId', 'action'];
    }
}
