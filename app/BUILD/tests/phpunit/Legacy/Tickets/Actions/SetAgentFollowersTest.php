<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\SetAgentFollowers;
use Application\DeskPRO\Tickets\ExecutorContext;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;

class SetAgentFollowersTest extends DeskProTestCase
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private function getMockContainer()
    {
        if ($this->container) {
            return $this->container;
        }
        $this->container = ContainerMock::create()->withAgentData()->get();

        return $this->container;
    }

    public function testAdd()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $ticket->addParticipantPerson($this->getMockContainer()->getAgentData()->get(1));
        $ticket->addParticipantPerson($this->getMockContainer()->getAgentData()->get(10));
        $ticket->addParticipantPerson($this->getMockContainer()->getAgentData()->get(20));

        $action = new SetAgentFollowers(['add_agent_ids' => [2, 3], 'remove_agent_ids' => [10, 20]]);
        $action->setContainer($this->getMockContainer());
        $action->applyAction($ticket, $exec);

        $this->assertEquals(3, count($ticket->getAgentParticipants()));
        $this->assertFalse($ticket->hasParticipantPerson(10));
        $this->assertTrue($ticket->hasParticipantPerson(2) !== false);
        $this->assertTrue($ticket->hasParticipantPerson(1) !== false);
    }

    public function testInvalid()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $ticket->addParticipantPerson($this->getMockContainer()->getAgentData()->get(1));
        $ticket->addParticipantPerson($this->getMockContainer()->getAgentData()->get(10));
        $ticket->addParticipantPerson($this->getMockContainer()->getAgentData()->get(20));

        $action = new SetAgentFollowers(['add_agent_ids' => [120], 'remove_agent_ids' => [121]]);
        $action->setContainer($this->getMockContainer());
        $action->applyAction($ticket, $exec);

        $this->assertEquals(3, count($ticket->getAgentParticipants()));
        $this->assertTrue($ticket->hasParticipantPerson(1) !== false);
        $this->assertFalse($ticket->hasParticipantPerson(120));
    }
}
