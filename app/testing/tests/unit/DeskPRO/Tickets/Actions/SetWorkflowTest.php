<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Tickets\Actions\SetWorkflow;
use DpTestingMocks\ContainerMock;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

class SetWorkflowTest extends \DpUnitTestCase
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
        $this->container = ContainerMock::create()->withTicketWorkflows()->get();

        return $this->container;
    }

    public function testSet()
    {
        $ticket = new Ticket();
        $ticket->workflow = $this->getMockContainer()->getTicketWorkflows()->getById(1);
        $exec   = new ExecutorContext();

        $action = new SetWorkflow(array('workflow_id' => 55));
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertInstanceOf('Application\\DeskPRO\\Entity\\TicketWorkflow', $ticket->workflow);
        $this->assertEquals(55, $ticket->workflow->id);
    }

    public function testSetNull()
    {
        $ticket = new Ticket();
        $ticket->workflow = $this->getMockContainer()->getTicketWorkflows()->getById(1);
        $exec   = new ExecutorContext();

        $action = new SetWorkflow(array('workflow_id' => 0));
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertNull($ticket->workflow);
    }

    public function testNoop()
    {
        $ticket = new Ticket();
        $ticket->workflow = $this->getMockContainer()->getTicketWorkflows()->getById(55);

        $exec = new ExecutorContext();

        $action = new SetWorkflow(array('workflow_id' => 55));
        $action->setContainer($this->container);

        $this->assertTrue($action->isNoop($ticket, $exec));
    }

    public function testInvalid()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $action = new SetWorkflow(array('workflow_id' => 200));
        $action->setContainer($this->getMockContainer());
        $action->applyAction($ticket, $exec);

        $this->assertNull($ticket->workflow);
    }
}
