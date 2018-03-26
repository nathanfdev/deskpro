<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\SetPriority;
use Application\DeskPRO\Tickets\ExecutorContext;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;

class SetPriorityTest extends DeskProTestCase
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
        $this->container = ContainerMock::create()->withTicketPriorities()->get();

        return $this->container;
    }

    public function testSet()
    {
        $ticket           = new Ticket();
        $ticket->priority = $this->getMockContainer()->getTicketPriorities()->getById(1);
        $exec             = new ExecutorContext();

        $action = new SetPriority(['priority_id' => 55]);
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertInstanceOf('Application\\DeskPRO\\Entity\\TicketPriority', $ticket->priority);
        $this->assertEquals(55, $ticket->priority->id);
    }

    public function testSetNull()
    {
        $ticket           = new Ticket();
        $ticket->priority = $this->getMockContainer()->getTicketPriorities()->getById(1);
        $exec             = new ExecutorContext();

        $action = new SetPriority(['priority_id' => 0]);
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertNull($ticket->priority);
    }

    public function testNoop()
    {
        $ticket           = new Ticket();
        $ticket->priority = $this->getMockContainer()->getTicketPriorities()->getById(55);

        $exec = new ExecutorContext();

        $action = new SetPriority(['priority_id' => 55]);
        $action->setContainer($this->container);

        $this->assertTrue($action->isNoop($ticket, $exec));
    }

    public function testInvalid()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new SetPriority(['priority_id' => 200]);
        $action->setContainer($this->getMockContainer());
        $action->applyAction($ticket, $exec);

        $this->assertNull($ticket->priority);
    }
}
