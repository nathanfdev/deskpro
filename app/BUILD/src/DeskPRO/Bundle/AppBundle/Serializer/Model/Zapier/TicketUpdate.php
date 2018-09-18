<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Zapier;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Zapier\TicketUpdate as TicketUpdateEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket as TicketModel;

class TicketUpdate extends TicketModel
{
    /**
     * @var array
     */
    private $changes;

    /**
     * @var Person
     */
    private $performer;

    /**
     * Constructor.
     *
     * @param $ticketUpdate
     */
    public function __construct(TicketUpdateEntity $ticketUpdate)
    {
        parent::__construct($ticketUpdate->getTicket());

        $this->changes   = $ticketUpdate->getChanges();
        $this->performer = $ticketUpdate->getPerformer();
    }
}
