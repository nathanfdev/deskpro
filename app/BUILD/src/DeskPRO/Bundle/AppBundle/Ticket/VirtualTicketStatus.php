<?php

namespace DeskPRO\Bundle\AppBundle\Ticket;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;

class VirtualTicketStatus extends TicketStatus
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return App::getTranslator()->phrase('agent.tickets.status_'.$this->getStatusType());
    }
}
