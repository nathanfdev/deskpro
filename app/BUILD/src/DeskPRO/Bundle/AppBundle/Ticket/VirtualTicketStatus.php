<?php

namespace DeskPRO\Bundle\AppBundle\Ticket;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;

class VirtualTicketStatus extends TicketStatus
{
    /**
     * Array of VirtualTicketStatus instances for each built-in status. We re-use the instances
     * to retain object identity.
     *
     * You still want to use TicketStatusDataService to load them usually because the children
     * would be hydrated here.
     *
     * @var VirtualTicketStatus[]
     */
    private static $ticketStatuses;

    /**
     * @param string $id
     *
     * @return VirtualTicketStatus
     */
    public static function getById($id)
    {
        if (!self::$ticketStatuses) {
            self::$ticketStatuses = [
                TicketStatus::STATUS_TYPE_AWAITING_AGENT => new self(TicketStatus::STATUS_TYPE_AWAITING_AGENT),
                TicketStatus::STATUS_TYPE_AWAITING_USER  => new self(TicketStatus::STATUS_TYPE_AWAITING_USER),
                TicketStatus::STATUS_TYPE_PENDING        => new self(TicketStatus::STATUS_TYPE_PENDING),
                TicketStatus::STATUS_TYPE_RESOLVED       => new self(TicketStatus::STATUS_TYPE_RESOLVED),
                TicketStatus::STATUS_TYPE_ARCHIVED       => new self(TicketStatus::STATUS_TYPE_ARCHIVED),
                TicketStatus::STATUS_TYPE_HIDDEN         => new self(TicketStatus::STATUS_TYPE_HIDDEN),
            ];
        }

        if (!isset(self::$ticketStatuses[$id])) {
            throw new \InvalidArgumentException();
        }

        return self::$ticketStatuses[$id];
    }

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
