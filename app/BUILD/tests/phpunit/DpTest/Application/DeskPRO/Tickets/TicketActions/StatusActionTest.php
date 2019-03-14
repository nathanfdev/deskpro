<?php

namespace DpTest\DeskPRO\Application\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketActions\StatusAction;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketStatusDataService;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;
use Mockery as m;

class StatusActionTest extends DeskProTestCase
{
    /**
     * @var DeskproContainer
     */
    protected $containerBefore;

    public function setUp()
    {
        $this->containerBefore = App::$container;
    }

    public function tearDown()
    {
        App::$container = $this->containerBefore;
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testIsValidStatus_ValidCase()
    {
        // GIVEN
        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('isValidStatusCode')->andReturn(true);
        App::$container = ContainerMock::create()->withTicketStatusesMock($statusesMock)->get();

        // WHEN / THEN
        new StatusAction('hidden.2');
        new StatusAction('hidden.2222'); // check fallback
    }

    public function testIsValidStatus_NotValidCase()
    {
        // GIVEN
        // GIVEN
        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('isValidStatusCode')->andReturn(false);
        App::$container = ContainerMock::create()->withTicketStatusesMock($statusesMock)->get();

        // WHEN
        try {
            new StatusAction('wrongstatus');
        } catch (\Exception $e) {
            $expectedException = $e;
        }

        // THEN
        $this->assertInstanceOf(\InvalidArgumentException::class, $expectedException);
    }

    public function testApply()
    {
        // GIVEN
        $deletedStatus = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $deletedStatus->setId(2);
        $deletedStatus->setSysId(TicketStatus::SYS_ID_DELETED);

        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('isValidStatusCode')->andReturn(true);
        $statusesMock->shouldReceive('findStatusOrException')->with('hidden.2', false, true)->andReturn($deletedStatus);
        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withDb()
            ->withTicketStatusesMock($statusesMock)->get();

        $action = new StatusAction($deletedStatus->getStatusCode());
        $ticket = new Ticket();

        // WHEN
        $action->apply($ticket);

        // THEN
        $this->assertTrue($ticket->isDeleted());
    }
}
