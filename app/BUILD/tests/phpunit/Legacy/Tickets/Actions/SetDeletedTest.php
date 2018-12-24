<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\SetDeleted;
use Application\DeskPRO\Tickets\ExecutorContext;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketStatusDataService;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;
use Mockery as m;

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
        // GEIVEN
        $deletedStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $deletedStatus->setId(2);
        $deletedStatus->setSysId(TicketStatus::SYS_ID_DELETED);

        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('getDeletedStatus')->andReturn($deletedStatus);
        $containerMock = ContainerMock::create()
            ->withNullEm()
            ->withDb()
            ->withTicketStatusesMock($statusesMock)->get();

        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new SetDeleted();
        $action->setContainer($containerMock);

        // WHEN
        $action->applyAction($ticket, $exec);

        // THEN
        $this->assertEquals('hidden.2', $ticket->getStatusCode());
    }

    public function testNoop()
    {
        // GIVEN
        $deletedStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $deletedStatus->setId(2);
        $deletedStatus->setSysId(TicketStatus::SYS_ID_DELETED);

        $ticket = new Ticket();
        $ticket->setTicketStatus($deletedStatus);
        $exec = new ExecutorContext();

        $action = new SetDeleted();
        $this->assertTrue($action->isNoop($ticket, $exec));
    }
}
