<?php

namespace DpTest\DeskPRO\Application\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketSms;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DeskPRO\Bundle\AppBundle\Ticket\VirtualTicketStatus;
use Orb\Util\WorkHoursSet;
use Orb\Util\Testable\DateTime;
use DpTestSrc\TestBundle\Mock\ContainerMock;
use Mockery as m;

class TicketWaitingTimeWorkingHoursTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeskproContainer
     */
    protected $containerBefore;

    public function setUp()
    {
        $this->containerBefore = App::$container;
        DateTime::unsetTimestampState();

        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withBaseTicketStatusesMock()
            ->withBasicSettings()
            ->get();
    }

    public function tearDown()
    {
        DateTime::unsetTimestampState();
        App::$container = $this->containerBefore;
    }

    /**
     * @testWith    ["2020-03-18 02:00:00", "2020-03-18 09:00:00"]
     *              ["2020-03-18 11:20:00", "2020-03-18 11:20:01"]
     *              ["2020-03-20 22:20:00", "2020-03-23 09:00:00"]
     *
     * @param string $nowStr
     * @param string $expectedWaitingStartStr
     */
    public function testSetStatusAwaitingAgent($nowStr, $expectedWaitingStartStr)
    {
        // GIVEN
        $ticket = m::mock('Application\\DeskPRO\\Entity\\Ticket[getWorkHoursSet]')
            ->shouldAllowMockingProtectedMethods();
        $ticket->shouldReceive('getWorkHoursSet')->andReturn($this->getStandardWorkHoursSet());
        DateTime::setTimestampState((new \DateTime($nowStr))->getTimestamp());

        // WHEN
        $ticket->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_AGENT));

        // THEN
        $this->assertEquals(new \DateTime($expectedWaitingStartStr), $ticket->total_user_waiting_wh_start);
        $this->assertEquals(0, $ticket->total_user_waiting_wh);
        $this->assertEquals(0, $ticket->total_to_first_reply_wh);
    }

    public function testSetStatusAwaitingAgentMultipleTimes_shouldNotChangeStartDate()
    {
        // GIVEN
        $ticket = m::mock('Application\\DeskPRO\\Entity\\Ticket[getWorkHoursSet]')
            ->shouldAllowMockingProtectedMethods();

        $ticket->shouldReceive('getWorkHoursSet')->andReturn($this->getStandardWorkHoursSet());
        DateTime::setTimestampState((new \DateTime("2020-03-18 02:00:00"))->getTimestamp());

        // WHEN / THEN
        $ticket->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_AGENT));
        $startDate = $ticket->total_user_waiting_wh_start;
        $this->assertEquals(new \DateTime("2020-03-18 09:00:00"), $startDate);

        DateTime::setTimestampState((new \DateTime("2020-03-20 02:00:00"))->getTimestamp());
        $ticket->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_AGENT));
        $this->assertEquals($startDate, $ticket->total_user_waiting_wh_start);
    }

    /**
     * @testWith    [null,                  "2020-03-18 12:00:00", 0]
     *              ["2020-03-18 09:00:00", "2020-03-18 12:00:00", 10800]
     *              ["2020-03-18 09:00:00", "2020-03-17 02:00:00", 0]
     *              ["2020-03-18 09:00:00", "2020-03-19 02:00:00", 32400]
     *              ["2020-03-20 09:00:00", "2020-03-23 22:00:00", 64800]
     *
     * @param string $nowStr
     * @param string $expectedWaitingStartStr
     */
    public function testSetStatusAwaitingUser($waitingStartStr, $nowStr, $expectedUserWaiting)
    {
        // GIVEN
        $ticket = m::mock('Application\\DeskPRO\\Entity\\Ticket[getWorkHoursSet]')
            ->shouldAllowMockingProtectedMethods();
        $ticket->shouldReceive('getWorkHoursSet')->andReturn($this->getStandardWorkHoursSet());
        DateTime::setTimestampState((new \DateTime($nowStr))->getTimestamp());
        $ticket->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_AGENT));
        // hardcode overwrite
        $ticket->total_user_waiting_wh_start = new \DateTime($waitingStartStr);

        // WHEN
        $ticket->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_USER));

        // THEN
        $this->assertEquals($expectedUserWaiting, $ticket->getTotalUserWaitingWh());
        $this->assertNull($ticket->total_user_waiting_wh_start);
    }

    public function testSetStatusAwaitingUserMultipleTimes_ShouldSum()
    {
        // GIVEN
        $ticket = m::mock('Application\\DeskPRO\\Entity\\Ticket[getWorkHoursSet]')
            ->shouldAllowMockingProtectedMethods();
        $ticket->shouldReceive('getWorkHoursSet')->andReturn($this->getStandardWorkHoursSet());
        DateTime::setTimestampState((new \DateTime("2020-03-18 12:00:00"))->getTimestamp());
        $ticket->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_AGENT));
        // hardcode overwrite
        $ticket->total_user_waiting_wh_start = new \DateTime("2020-03-18 09:00:00");
        $ticket->total_user_waiting_wh = 10800;

        // WHEN/THEN
        $ticket->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_USER));
        $this->assertEquals(21600, $ticket->getTotalUserWaitingWh());
        $this->assertNull($ticket->total_user_waiting_wh_start);
    }

    public function testAddAgentMessage()
    {
        // GIVEN
        $ticket = m::mock('Application\\DeskPRO\\Entity\\Ticket[getWorkHoursSet]')
            ->shouldAllowMockingProtectedMethods();
        $ticket->shouldReceive('getWorkHoursSet')->andReturn($this->getStandardWorkHoursSet());
        // DateTime::setTimestampState((new \DateTime("2020-03-18 12:00:00"))->getTimestamp());
        $ticket->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_AGENT));
        // hardcode overwrite
        $ticket->date_created = new \DateTime("2020-03-17 03:00:00");

        $person = new Person();
        $person->is_agent = true;
        $message = new TicketMessage();
        $message->person = $person;
        $message->date_created = new \DateTime("2020-03-18 12:00:00");

        // WHEN
        $ticket->_is_new = false;
        $ticket->addMessage($message);

        // THEN
        $this->assertEquals(43200, $ticket->getTotalToFirstReplyWh());
    }

    public function testAddSmsMessage()
    {
        // GIVEN
        $ticket = m::mock('Application\\DeskPRO\\Entity\\Ticket[getWorkHoursSet]')
            ->shouldAllowMockingProtectedMethods();
        $ticket->shouldReceive('getWorkHoursSet')->andReturn($this->getStandardWorkHoursSet());
        DateTime::setTimestampState((new \DateTime("2020-03-18 12:00:00"))->getTimestamp());
        $ticket->setTicketStatus(VirtualTicketStatus::getById(TicketStatus::STATUS_TYPE_AWAITING_AGENT));
        // hardcode overwrite
        $ticket->date_created = new \DateTime("2020-03-17 03:00:00");

        $person = new Person();
        $person->is_agent = true;
        $message = new TicketSms('incoming');
        $message->person = $person;
        $message->date_created = new \DateTime("2020-03-18 12:00:00");

        // WHEN
        $ticket->addSmsMessage($message);

        // THEN
        $this->assertEquals(43200, $ticket->getTotalToFirstReplyWh());
    }

    public function dataProviderGetTotalUserWaitingWorkTime()
    {
        $data = [];

        // New way calculations

        $data[] = [[
            'now' => '2020-07-13 15:00:00',
            'status' => TicketStatus::STATUS_TYPE_AWAITING_AGENT,
            'waiting_times' => [],
            'date_user_waiting' => null,
            'total_user_waiting_wh' => 1000,
            'total_user_waiting_wh_start' => null,
            'expected_result' => 1000
        ]];

        $data[] = [[
            'now' => '2020-07-13 15:00:00',
            'status' => TicketStatus::STATUS_TYPE_AWAITING_AGENT,
            'waiting_times' => [],
            'date_user_waiting' => null,
            'total_user_waiting_wh' => 1000,
            'total_user_waiting_wh_start' => '2020-07-13 12:00:00',
            'expected_result' => 1000 + 3 * 60 * 60
        ]];

        // tickets with old `waiting_times` format

        $data[] = [[
            'now' => '2020-07-13 15:00:00',
            'status' => TicketStatus::STATUS_TYPE_AWAITING_USER,
            'waiting_times' => [
                ['type' => 'user', 'start' => strtotime('2020-07-13 09:00:00'), 'end' => strtotime('2020-07-13 10:00:00')],
                ['type' => 'agent', 'start' => strtotime('2020-07-13 10:00:00'), 'end' => strtotime('2020-07-13 12:00:00')],
            ],
            'date_user_waiting' => null,
            'total_user_waiting_wh' => null,
            'total_user_waiting_wh_start' => null,
            'expected_result' => 1 * 60 * 60
        ]];
        $data[] = [[
            'now' => '2020-07-13 15:00:00',
            'status' => TicketStatus::STATUS_TYPE_AWAITING_AGENT,
            'waiting_times' => [
                ['type' => 'user', 'start' => strtotime('2020-07-13 09:00:00'), 'end' => strtotime('2020-07-13 10:00:00')],
                ['type' => 'agent', 'start' => strtotime('2020-07-13 10:00:00'), 'end' => strtotime('2020-07-13 12:00:00')],
            ],
            'date_user_waiting' => '2020-07-13 12:00:00',
            'total_user_waiting_wh' => null,
            'total_user_waiting_wh_start' => null,
            'expected_result' => 4 * 60 * 60
        ]];

        // tickets with mixed `waiting_times` format
        $data[] = [[
            'now' => '2020-07-13 15:00:00',
            'status' => TicketStatus::STATUS_TYPE_AWAITING_AGENT,
            'waiting_times' => [
                ['type' => 'user', 'start' => strtotime('2020-07-13 09:00:00'), 'end' => strtotime('2020-07-13 10:00:00')],
                ['ticket_status' => 'awaiting_user', 'start' => strtotime('2020-07-13 10:00:00'), 'end' => strtotime('2020-07-13 11:00:00')],
                ['ticket_status' => 'awaiting_agent', 'start' => strtotime('2020-07-13 11:00:00'), 'end' => strtotime('2020-07-13 12:00:00')],
                ['ticket_status' => 'awaiting_user', 'start' => strtotime('2020-07-13 12:00:00'), 'end' => strtotime('2020-07-13 13:00:00')],
                // unknown status
                ['ticket_status' => 'awaiting_user_non_exist', 'start' => strtotime('2020-07-13 12:00:00'), 'end' => strtotime('2020-07-13 13:00:00')],
            ],
            'date_user_waiting' => null,
            'total_user_waiting_wh' => null,
            'total_user_waiting_wh_start' => null,
            'expected_result' => 2 * 60 * 60
        ]];

        return $data;
    }

    /**
     * @dataProvider dataProviderGetTotalUserWaitingWorkTime
     */
    public function testGetTotalUserWaitingWorkTime($data)
    {
        // GIVEN
        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withBaseTicketStatusesMock()
            ->withSettings()
            ->get();

        DateTime::setTimestampState((new \DateTime($data['now']))->getTimestamp());

        $ticket = m::mock('Application\\DeskPRO\\Entity\\Ticket[getWorkHoursSet]')
            ->shouldAllowMockingProtectedMethods();
        $ticket->shouldReceive('getWorkHoursSet')->andReturn($this->getStandardWorkHoursSet());

        $ticket->setTicketStatus(VirtualTicketStatus::getById($data['status']));
        $ticket->waiting_times = $data['waiting_times'];
        $ticket->date_user_waiting = $data['date_user_waiting']
            ? new \DateTime($data['date_user_waiting'])
            : null;
        $ticket->total_user_waiting_wh = $data['total_user_waiting_wh'];
        $ticket->total_user_waiting_wh_start = $data['total_user_waiting_wh_start']
            ? new \DateTime($data['total_user_waiting_wh_start'])
            : null;

        // WHEN
        $time = $ticket->getTotalUserWaitingWorkTime();

        // THEN
        $this->assertEquals($data['expected_result'], $time);
    }

    /**
     *
     * @return WorkHoursSet
     */
    protected function getStandardWorkHoursSet()
    {
        return new WorkHoursSet(
            9 * 3600,
            18 * 3600,
            [1, 2, 3, 4, 5],
            'UTC',
            []
        );
    }
}
