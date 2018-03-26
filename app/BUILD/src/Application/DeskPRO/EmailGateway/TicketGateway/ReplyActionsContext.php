<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

class ReplyActionsContext
{
    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    public $ticket;

    /**
     * @var \Application\DeskPRO\Entity\TicketMessage
     */
    public $message;
}
