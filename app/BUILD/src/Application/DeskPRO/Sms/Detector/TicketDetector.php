<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Sms\Detector;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;

class TicketDetector
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * find a ticket based on who sent the sms
     * finds a ticket to reply to using our logic.
     *
     * return a ticket if this person is probably replying to that ticket
     * or return null if this person is initiating a new ticket
     *
     * @param Person $from_person
     *
     * @return \Application\DeskPRO\Entity\Ticket|null
     */
    public function detectBySender(Person $from_person = null)
    {
        if (!$from_person) {
            return;
        }

        $ticket = $this->em->getRepository('DeskPRO:Ticket')->findMostRecentSmsTicketFromPerson(
            $from_person,
            new \DateTime('now - 3 days')
        );

        return $ticket;
    }
}
