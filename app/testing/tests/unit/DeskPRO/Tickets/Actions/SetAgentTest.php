<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Tickets\Actions\SetAgent;
use DpTestingMocks\ContainerMock;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

class SetAgentTest extends \DpUnitTestCase
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
        if ($this->container) return $this->container;
        $this->container = ContainerMock::create()->withAgentData()->get();

        return $this->container;
    }

    public function testSetAgent()
    {
        $ticket = new Ticket();
        $ticket->agent = $this->getMockContainer()->getAgentData()->get(1);
        $exec   = new ExecutorContext();

        $action = new SetAgent(array('agent_id' => 55));
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertInstanceOf('Application\\DeskPRO\\Entity\\Person', $ticket->agent);
        $this->assertEquals(55, $ticket->agent->id);
    }

    public function testSetUnassigned()
    {
        $ticket = new Ticket();
        $ticket->agent = $this->getMockContainer()->getAgentData()->get(1);
        $exec   = new ExecutorContext();

        $action = new SetAgent(array('agent_id' => 0));
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertNull($ticket->agent);
    }

    public function testSetSelf()
    {
        $ticket = new Ticket();
        $ticket->agent = $this->getMockContainer()->getAgentData()->get(1);

        $exec = new ExecutorContext();
        $exec->setPersonContext($this->getMockContainer()->getAgentData()->get(55));

        $action = new SetAgent(array('agent_id' => -1));
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertInstanceOf('Application\\DeskPRO\\Entity\\Person', $ticket->agent);
        $this->assertEquals(55, $ticket->agent->id);
    }

    public function testNoop()
    {
        $ticket = new Ticket();
        $ticket->agent = $this->getMockContainer()->getAgentData()->get(55);

        $exec = new ExecutorContext();

        $action = new SetAgent(array('agent_id' => 55));
        $action->setContainer($this->container);

        $this->assertTrue($action->isNoop($ticket, $exec));
    }

    public function testInvalid()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $action = new SetAgent(array('agent_id' => 200));
        $action->setContainer($this->getMockContainer());
        $action->applyAction($ticket, $exec);

        $this->assertNull($ticket->agent);
    }
}
