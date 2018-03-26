<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\SetDeleted;
use Application\DeskPRO\Tickets\ExecutorContext;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;

class SetDeletedTest extends DeskProTestCase
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
        $this->container = ContainerMock::create()->withDb()->get();

        return $this->container;
    }

    public function testDelete()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new SetDeleted();
        $action->setContainer($this->getMockContainer());
        $action->applyAction($ticket, $exec);

        $this->assertEquals('hidden.deleted', $ticket->getStatusCode());
    }

    public function testNoop()
    {
        $ticket         = new Ticket();
        $ticket->status = 'hidden.deleted';
        $exec           = new ExecutorContext();

        $action = new SetDeleted();
        $this->assertTrue($action->isNoop($ticket, $exec));
    }
}
