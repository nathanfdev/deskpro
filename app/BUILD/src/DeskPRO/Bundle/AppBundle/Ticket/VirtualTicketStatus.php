<?php

namespace DeskPRO\Bundle\AppBundle\Ticket;

use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;

class VirtualTicketStatus extends TicketStatus
{
    public function getId()
    {
        return null;
    }
}
