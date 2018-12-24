<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\SetStatus;
use Application\DeskPRO\Tickets\ExecutorContext;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketStatusDataService;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;
use Mockery as m;

class SetStatusTest extends DeskProTestCase
{
    public function testSet()
    {
        // GIVEN
        $awaitingStatus = new TicketStatus(TicketStatus::STATUS_TYPE_AWAITING_USER);

        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('isValidStatusCode')->andReturn(true);
        $statusesMock->shouldReceive('findStatusOrException')->with('awaiting_user', false, true)->andReturn($awaitingStatus);
        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withTicketStatusesMock($statusesMock)->get();

        $ticket         = new Ticket();
        $ticket->status = 'awaiting_agent';
        $exec           = new ExecutorContext();

        $action = new SetStatus(['status' => 'awaiting_user']);

        // WHEN
        $action->applyAction($ticket, $exec);

        // THEN
        $this->assertEquals('awaiting_user', $ticket->getStatusCode());
    }

    public function testSet2()
    {
        // GIVEN
        $deletedStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $deletedStatus->setSysId(TicketStatus::SYS_ID_DELETED);
        $deletedStatus->setId(2);

        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('isValidStatusCode')->andReturn(true);
        $statusesMock->shouldReceive('findStatusOrException')->with('hidden.deleted', false, true)->andReturn($deletedStatus);
        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withTicketStatusesMock($statusesMock)->get();

        $ticket         = new Ticket();
        $ticket->status = 'awaiting_agent';
        $exec           = new ExecutorContext();

        $action = new SetStatus(['status' => 'hidden.deleted']);

        // WHEN
        $action->applyAction($ticket, $exec);

        // THEN
        $this->assertEquals('hidden.2', $ticket->getStatusCode());
    }

    /**
     * @expectedException \Orb\Util\CheckedOptionsException
     */
    public function testInvalid()
    {
        // GIVEN
        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('isValidStatusCode')->andReturn(false);
        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withTicketStatusesMock($statusesMock)->get();

        $ticket         = new Ticket();
        $ticket->status = 'awaiting_agent';

        $exec = new ExecutorContext();

        // WHEN/THEN
        $action = new SetStatus(['status' => 'asdadasdasdsad']);

        $this->assertTrue($action->isNoop($ticket, $exec));
    }

    public function testNoop2()
    {
        // GIVEN
        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('isValidStatusCode')->andReturn(true);
        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withTicketStatusesMock($statusesMock)->get();

        $ticket         = new Ticket();
        $ticket->status = 'awaiting_agent';

        $exec = new ExecutorContext();

        $action = new SetStatus(['status' => 'awaiting_agent']);

        // WHEN / THEN
        $this->assertTrue($action->isNoop($ticket, $exec));
    }
}
