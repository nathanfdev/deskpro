<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line;

use Application\DeskPRO\Entity\TicketLog;

class TIcketLogLine implements LineInterface
{
    /**
     * @var TicketLog
     */
    private $log;

    /**
     * @param TicketLog $log
     */
    public function __construct(TicketLog $log)
    {
        $this->log = $log;
    }

    /**
     * @return TicketLog
     */
    public function getLog()
    {
        return $this->log;
    }

    public function getPerson()
    {
        return;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->log->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->log->action_type;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->log->date_created;
    }
}
