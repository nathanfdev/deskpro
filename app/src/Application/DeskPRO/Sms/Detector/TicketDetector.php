<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
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
     * finds a ticket to reply to using our logic
     *
     * return a ticket if this person is probably replying to that ticket
     * or return null if this person is initiating a new ticket
     *
     * @param  Person                                  $from_person
     * @return \Application\DeskPRO\Entity\Ticket|null
     */
    public function detectBySender(Person $from_person = null)
    {
        if (!$from_person) {
            return null;
        }

        $ticket = $this->em->getRepository('DeskPRO:Ticket')->findMostRecentSmsTicketFromPerson(
            $from_person,
            new \DateTime("now - 3 days")
        );

        return $ticket;
    }
}
