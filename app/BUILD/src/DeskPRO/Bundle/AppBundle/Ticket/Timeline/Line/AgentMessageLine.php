<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line;

use Application\DeskPRO\Entity\TicketMessage;

class AgentMessageLine implements LineInterface
{
    /**
     * @var TicketMessage
     */
    private $message;

    /**
     * @param TicketMessage $message
     */
    public function __construct(TicketMessage $message)
    {
        $this->message = $message;
    }

    /**
     * @return TicketMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function getPerson()
    {
        return $this->message->getPerson();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'agent_message';
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->message->getDateCreated();
    }
}
