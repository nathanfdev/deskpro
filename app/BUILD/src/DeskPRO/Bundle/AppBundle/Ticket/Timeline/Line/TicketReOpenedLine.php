<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line;

use Application\DeskPRO\Entity\Person;

class TicketReOpenedLine implements LineInterface
{
    /**
     * @var Person
     */
    private $who;

    /**
     * @var \DateTime
     */
    private $when;

    /**
     * @param Person    $who
     * @param \DateTime $when
     */
    public function __construct(\DateTime $when, Person $who = null)
    {
        $this->who  = $who;
        $this->when = $when;
    }

    /**
     * @return \DateTime
     */
    public function getPerson()
    {
        return $this->who;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'ticket_reopened';
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->when;
    }
}
