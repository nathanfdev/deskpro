<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Tickets\Actions\SetDepartment;
use DpTestingMocks\ContainerMock;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

class SetDepartmentTest extends \DpUnitTestCase
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
        $this->container = ContainerMock::create()->withTicketDepartments()->get();

        return $this->container;
    }

    public function testSet()
    {
        $ticket = new Ticket();
        $ticket->department = $this->getMockContainer()->getTicketDepartments()->getById(1);
        $exec   = new ExecutorContext();

        $action = new SetDepartment(array('department_id' => 55));
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertInstanceOf('Application\\DeskPRO\\Entity\\Department', $ticket->department);
        $this->assertEquals(55, $ticket->department->id);
    }

    public function testSetNullNoop()
    {
        $ticket = new Ticket();
        $ticket->department = $this->getMockContainer()->getTicketDepartments()->getById(1);
        $exec   = new ExecutorContext();

        $action = new SetDepartment(array('department_id' => 0));
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertInstanceOf('Application\\DeskPRO\\Entity\\Department', $ticket->department);
        $this->assertEquals(1, $ticket->department->id);
    }

    public function testNoop()
    {
        $ticket = new Ticket();
        $ticket->department = $this->getMockContainer()->getTicketDepartments()->getById(55);

        $exec = new ExecutorContext();

        $action = new SetDepartment(array('department_id' => 55));
        $action->setContainer($this->container);

        $this->assertTrue($action->isNoop($ticket, $exec));
    }

    public function testInvalid()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        $action = new SetDepartment(array('department_id' => 200));
        $action->setContainer($this->getMockContainer());
        $action->applyAction($ticket, $exec);

        $this->assertNull($ticket->department);
    }
}
