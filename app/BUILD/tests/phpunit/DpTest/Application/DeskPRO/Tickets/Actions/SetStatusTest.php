<?php

namespace DpTest\DeskPRO\Application\Tickets\Actions;

use Application\DeskPRO\App;
use Application\DeskPRO\Tickets\Actions\SetStatus;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketStatusDataService;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;
use Mockery as m;
use Orb\Util\CheckedOptionsException;

class SetStatusTest extends DeskProTestCase
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

    public function testIsValidStatus_ValidCase()
    {
        // GIVEN
        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('isValidStatusCode')->andReturn(true);
        App::$container = ContainerMock::create()->withTicketStatusesMock($statusesMock)->get();

        $action = new SetStatus(['status' => 'hidden.2']);

        // WHEN / THEN
        $this->assertTrue($action->isValidStatus('hidden.2'));
    }

    public function testIsValidStatus_NotValidCase()
    {
        // GIVEN
        $statusesMock = m::mock(TicketStatusDataService::class);
        $statusesMock->shouldReceive('isValidStatusCode')->andReturn(false);
        App::$container = ContainerMock::create()->withTicketStatusesMock($statusesMock)->get();

        $expectedException = null;

        // WHEN
        try {
            $action = new SetStatus(['status' => 'hidden.22']);
        } catch (\Exception $e) {
            $expectedException = $e;
        }

        // THEN
        $this->assertInstanceOf(CheckedOptionsException::class, $expectedException);
    }
}
