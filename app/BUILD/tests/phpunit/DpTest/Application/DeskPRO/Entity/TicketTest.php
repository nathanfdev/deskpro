<?php

namespace DpTest\DeskPRO\Application\Entity;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DpTest\PortalTestCase;

class TicketTest extends PortalTestCase
{
    public function setUp()
    {
        $this->installDataSet('fresh', true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage is not an agent
     */
    public function testSetNotAgentForNewTicket()
    {
        $person = new Person();
        $person->setIsAgent(false);

        $this->getEntityManager()->persist($person);

        $ticket = new Ticket();
        $ticket->setAgent($person);

        $this->getEntityManager()->persist($ticket);
        $this->getEntityManager()->flush();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage is not an agent
     */
    public function testSetNotAgentOnTicketUpdate()
    {
        $ticket = new Ticket();
        $ticket->setSubject('subject');

        $this->getEntityManager()->persist($ticket);
        $this->getEntityManager()->flush();

        $person = new Person();
        $person->setIsAgent(false);

        $this->getEntityManager()->persist($person);

        $ticket->setAgent($person);

        $this->getEntityManager()->persist($ticket);
        $this->getEntityManager()->flush();
    }
}
