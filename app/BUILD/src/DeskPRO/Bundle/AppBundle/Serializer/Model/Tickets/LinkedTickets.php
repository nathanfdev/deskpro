<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\Ticket as TicketEntity;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class LinkedTickets.
 */
class LinkedTickets
{
    /**
     * Parent ticket.
     *
     * @JMS\Type("Application\DeskPRO\Entity\Ticket")
     *
     * @var TicketEntity
     */
    private $parent;

    /**
     * Ticket array.
     *
     * @JMS\Type("collection<Application\DeskPRO\Entity\Ticket>")
     *
     * @var ArrayCollection
     */
    private $siblings;

    /**
     * Ticket array.
     *
     * @JMS\Type("collection<Application\DeskPRO\Entity\Ticket>")
     *
     * @var ArrayCollection
     */
    private $children;

    /**
     * total count of linked tickets.
     *
     * @var int
     */
    private $count;

    /**
     * LinkedTickets constructor.
     *
     * @param TicketEntity $ticket
     */
    public function __construct(TicketEntity $ticket)
    {
        $this->parent   = $ticket->getParentTicket();
        $this->siblings = $ticket->getSiblingsTickets();
        $this->children = $ticket->getChildrenTickets();
        $this->count    = array_sum([$this->parent ? 1 : 0, count($this->siblings), count($this->children)]);
    }
}
