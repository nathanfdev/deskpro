<?php

namespace DpTest\DeskPRO\Application\Entity;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use Orb\Util\Testable\DateTime;

class TicketSetStatusTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        DateTime::unsetTimestampState();
    }

    public function tearDown()
    {
        DateTime::unsetTimestampState();
    }

    public function dataProvider()
    {
        $data = [];

        // Create ticket and set awaiting_agent status
        $data[] = [[
            'now' => '2020-06-22 12:00:00',
            'ticket' => [],
            'setStatus' => new TicketStatus('awaiting_agent'),
            'expected' => [
                'status' => 'awaiting_agent',
                'total_user_waiting' => 0,
                'date_user_waiting' => '2020-06-22 12:00:00',
                'date_agent_waiting' => null,
                'date_status' => '2020-06-22 12:00:00',
            ]
        ]];

        // Set awaiting_user status
        $data[] = [[
            'now' => '2020-06-22 12:00:00',
            'ticket' => [
                'status' => new TicketStatus('awaiting_agent'),
                'date_user_waiting' => '2020-06-21 12:00:00',
                'date_agent_waiting' => null,
                'total_user_waiting' => 0,
            ],
            'setStatus' => new TicketStatus('awaiting_user'),
            'expected' => [
                'status' => 'awaiting_user',
                'total_user_waiting' => 24 * 60 * 60,
                'date_user_waiting' => null,
                'date_agent_waiting' => '2020-06-22 12:00:00',
                'date_status' => '2020-06-22 12:00:00',
            ]
        ]];

        // Set Pending status with option to count time toward agent
        // Means that in pending status AGent is waiting for user response
        $data[] = [[
            'now' => '2020-06-22 12:00:00',
            'ticket' => [
                'status' => new TicketStatus('awaiting_agent'),
                'date_user_waiting' => '2020-06-21 12:00:00',
                'date_agent_waiting' => null,
                'total_user_waiting' => 0,
            ],
            'setStatus' => (new TicketStatus('pending'))->setPendingWaitingTimeMode(TicketStatus::PENDING_WAITING_TIME_MODE_AGENT),
            'expected' => [
                'status' => 'pending',
                'total_user_waiting' => 24 * 60 * 60,
                'date_user_waiting' => null,
                'date_agent_waiting' => '2020-06-22 12:00:00',
                'date_status' => '2020-06-22 12:00:00',
            ]
        ]];

        // Set Pending status with option to count time as NONE
        // Means agent and user don't wait
        $data[] = [[
            'now' => '2020-06-22 12:00:00',
            'ticket' => [
                'status' => new TicketStatus('awaiting_agent'),
                'date_user_waiting' => '2020-06-21 12:00:00',
                'date_agent_waiting' => null,
                'total_user_waiting' => 0,
            ],
            'setStatus' => (new TicketStatus('pending'))->setPendingWaitingTimeMode(TicketStatus::PENDING_WAITING_TIME_MODE_NONE),
            'expected' => [
                'status' => 'pending',
                'total_user_waiting' => 24 * 60 * 60,
                'date_user_waiting' => null,
                'date_agent_waiting' => null,
                'date_status' => '2020-06-22 12:00:00',
            ]
        ]];

        // Set Pending status with option to count time toward USER
        // should not change waiting times for already waiting ticket
        $data[] = [[
            'now' => '2020-06-22 12:00:00',
            'ticket' => [
                'status' => new TicketStatus('awaiting_agent'),
                'date_user_waiting' => '2020-06-21 12:00:00',
                'date_agent_waiting' => null,
                'total_user_waiting' => 0,
            ],
            'setStatus' => (new TicketStatus('pending'))->setPendingWaitingTimeMode(TicketStatus::PENDING_WAITING_TIME_MODE_USER),
            'expected' => [
                'status' => 'pending',
                'total_user_waiting' => 0,
                'date_user_waiting' => '2020-06-21 12:00:00',
                'date_agent_waiting' => null,
                'date_status' => '2020-06-22 12:00:00',
            ]
        ]];

        // Set Pending status with option to count time toward USER
        $data[] = [[
            'now' => '2020-06-22 12:00:00',
            'ticket' => [
                'status' => new TicketStatus('awaiting_user'),
                'date_user_waiting' => null,
                'date_agent_waiting' => '2020-06-21 12:00:00',
                'total_user_waiting' => 0,
            ],
            'setStatus' => (new TicketStatus('pending'))->setPendingWaitingTimeMode(TicketStatus::PENDING_WAITING_TIME_MODE_USER),
            'expected' => [
                'status' => 'pending',
                'total_user_waiting' => 0,
                'date_user_waiting' => '2020-06-22 12:00:00',
                'date_agent_waiting' => null,
                'date_status' => '2020-06-22 12:00:00',
            ]
        ]];

        return $data;
    }

    /**
     * @dataProvider dataProvider
     *
     * @param array $data
     */
    public function testSetStatus($data)
    {
        // GIVEN

        DateTime::setTimestampState((new \DateTime($data['now']))->getTimestamp());

        $ticket = new Ticket();
        if ($data['ticket']) {
            $ticket->setTicketStatus($data['ticket']['status']);
            $ticket->total_user_waiting = $data['ticket']['total_user_waiting'];
            $ticket->date_agent_waiting = $data['ticket']['date_agent_waiting']
                                            ? new \DateTime($data['ticket']['date_agent_waiting'])
                                            : null;
            $ticket->date_user_waiting = $data['ticket']['date_user_waiting']
                                            ? new \DateTime($data['ticket']['date_user_waiting'])
                                            : null;
        }
        // hardcode overwrite just to check that this field will be initialized during status change
        $ticket->date_status = null;

        // WHEN
        $ticket->setTicketStatus($data['setStatus']);

        // THEN
        $this->assertEquals(
            $data['expected']['status'],
            $ticket->getTicketStatus()->getStatusCode(),
            "Wrong status"
        );
        $this->assertEquals(
            $data['expected']['total_user_waiting'],
            $ticket->total_user_waiting,
            "Wrong total user waiting"
        );
        $this->assertEquals(
            $data['expected']['date_user_waiting'] ? new \DateTime($data['expected']['date_user_waiting']) : null,
            $ticket->date_user_waiting,
            "Wrong date user waiting"
        );
        $this->assertEquals(
            $data['expected']['date_agent_waiting'] ? new \DateTime($data['expected']['date_agent_waiting']) : null,
            $ticket->date_agent_waiting,
            "Wrong date agent waiting"
        );
        $this->assertEquals(
            $data['expected']['date_status'] ? new \DateTime($data['expected']['date_status']) : null,
            $ticket->date_status,
            "Wrong date status"
        );
    }
}