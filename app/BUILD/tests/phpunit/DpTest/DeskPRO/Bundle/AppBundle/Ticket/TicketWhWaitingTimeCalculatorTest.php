<?php

namespace DpTest\DeskPRO\Application\Entity;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\EntityRepository\TicketLog as TicketLogRepos;
use DeskPRO\Bundle\AppBundle\Ticket\TicketWhWaitingTimeCalculator;
use Orb\Util\WorkHoursSet;
use Orb\Util\Testable\DateTime;
use Doctrine\ORM\EntityManager;

use Mockery as m;

class TicketWhWaitingTimeCalculatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        DateTime::unsetTimestampState();
    }

    public function tearDown()
    {
        DateTime::unsetTimestampState();
    }

    public function calculateDataProvider()
    {
        $data = [];

        /**
         * ******************************************************************************************************
         * WORKING HOURS = 09:00 - 18:00 [1,2,3,4,5]
         * ******************************************************************************************************
         */

        // - User created ticket several days ago
        // - 'now' is during working hours
        //
        // Expected calcs:
        // --------------------------------------------------------------------------
        //  Day     Counted                 Action                  Waiting time Sum
        //          Work Time period
        // --------------------------------------------------------------------------
        //  16      09:00:00 - 18:00:00     08:00 - ticket created  9h
        //  17      09:00:00 - 18:00:00                             18h
        //  18      09:00:00 - 18:00:00                             27h
        //  19      09:00:00 - 18:00:00                             36h
        //  20      09:00:00 - 13:30:00                             40h + 30min
        //
        $data[] = [[
            'wh' => $this->getStandardWorkHoursSet(),
            'now' => '2020-03-20 13:30:00',
            'logs' => [
                // - User created ticket several days
                $this->createTicketLog('ticket_created', '2020-03-16 08:00:00'),
                $this->createTicketLog('message_created', '2020-03-16 08:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ]),
                // just a noise
                $this->createTicketLog('message_created', '2020-03-20 13:00:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 1
                ])
            ],
            'expected' => [
                'total_user_waiting_wh_start' => '2020-03-20 13:30:01', // because 'now' is in working time
                'total_user_waiting_wh' => 40 * 3600 + 30 * 60,
                'total_to_first_reply_wh' => 40 * 3600 + 30 * 60
            ]
        ]];

        //   Emulate ticket creation in agent interface by agent
        // - Agent created ticket some days ago (with ticket status = awaiting_user
        //   reply waiting time and waiting time should be 0
        // - 'now' is during working hours
        //
        // Expected calcs:
        // --------------------------------------------------------------------------
        //  Day     Counted                 Action                  Waiting time Sum
        //          Work Time period
        // --------------------------------------------------------------------------
        //  16      09:00:00 - 18:00:00     08:00 - ticket created  9h
        //
        $data[] = [[
            'wh' => $this->getStandardWorkHoursSet(),
            'now' => '2020-03-16 13:30:00',
            'logs' => [
                // - User created ticket several days
                $this->createTicketLog('ticket_created', '2020-03-16 08:00:00'),
                $this->createTicketLog('changed_status', '2020-03-16 08:00:00', [
                    'old_status' => 'awaiting_agent',
                    'new_status' => 'awaiting_user'
                ]),
                $this->createTicketLog('message_created', '2020-03-16 08:00:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 0
                ]),
            ],
            'expected' => [
                'total_user_waiting_wh_start' => null,
                'total_user_waiting_wh' => 0,
                'total_to_first_reply_wh' => 0
            ]
        ]];

        // - User created ticket several days
        // - 'now' is NOT during working hours
        //
        // Expected calcs:
        // --------------------------------------------------------------------------
        //  Day     Counted                 Action                  Waiting time Sum
        //          Work Time period
        // --------------------------------------------------------------------------
        //  16      09:00:00 - 18:00:00     08:00 - ticket created  9h
        //  17      09:00:00 - 18:00:00                             18h
        //  18      09:00:00 - 18:00:00                             27h
        //  19      09:00:00 - 18:00:00                             36h
        //  20      09:00:00 - 18:00:00                             45h
        //
        $data[] = [[
            'wh' => $this->getStandardWorkHoursSet(),
            'now' => '2020-03-20 20:30:00',
            'logs' => [
                // - User created ticket several days
                $this->createTicketLog('ticket_created', '2020-03-16 08:00:00'),
                $this->createTicketLog('message_created', '2020-03-16 08:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ])
            ],
            'expected' => [
                'total_user_waiting_wh_start' => '2020-03-23 09:00:00', // because 'now' is not in working time
                'total_user_waiting_wh' => 45 * 3600,
                'total_to_first_reply_wh' => 45 * 3600
            ]
        ]];

        // - User created ticket several days ago  (status = awaiting_agent)
        // - agent replied (status = awaiting_user)
        //
        // Expected calcs:
        // --------------------------------------------------------------------------
        //  Day     Counted                 Action                  Waiting time Sum
        //          Work Time period
        // --------------------------------------------------------------------------
        //  16      09:00:00 - 18:00:00     08:00 - ticket created  9h
        //  17      09:00:00 - 18:00:00                             18h
        //  18      09:00:00 - 18:00:00                             27h
        //  19      09:00:00 - 18:00:00                             36h
        //  20      09:00:00 - 13:30:00     13:30 - agent reply     40h + 30min
        //
        $data[] = [[
            'wh' => $this->getStandardWorkHoursSet(),
            'now' => '2020-03-20 15:00:00',
            'logs' => [
                // - User created ticket several days ago  (status = awaiting_agent)
                $this->createTicketLog('ticket_created', '2020-03-16 08:00:00'),
                $this->createTicketLog('message_created', '2020-03-16 08:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ]),
                // - agent replied (status = awaiting_user)
                $this->createTicketLog('message_created', '2020-03-20 13:30:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 0
                ]),
                $this->createTicketLog('changed_status', '2020-03-20 13:30:00', [
                    'old_status' => 'awaiting_agent',
                    'new_status' => 'awaiting_user'
                ]),
                // just a noise
                $this->createTicketLog('message_created', '2020-03-20 14:00:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 1
                ])
            ],
            'expected' => [
                'total_user_waiting_wh_start' => null,                  // because curr status is 'awaiting_user'
                'total_user_waiting_wh' => 40 * 3600 + 30 * 60,
                'total_to_first_reply_wh' => 40 * 3600 + 30 * 60
            ]
        ]];

        // - User created ticket several days ago (status = awaiting_agent)
        // - someone just changed status = awaiting_user without any agent reply
        //
        // Expected calcs:
        // --------------------------------------------------------------------------
        //  Day     Counted                 Action                  Waiting time Sum
        //          Work Time period
        // --------------------------------------------------------------------------
        //  16      09:00:00 - 18:00:00     08:00 - ticket created  9h
        //  17      09:00:00 - 18:00:00                             18h
        //  18      09:00:00 - 18:00:00                             27h
        //  19      09:00:00 - 18:00:00                             36h
        //  20      09:00:00 - 13:30:00     13:30 - status=aw_us    40h + 30min
        //
        $data[] = [[
            'wh' => $this->getStandardWorkHoursSet(),
            'now' => '2020-03-20 15:00:00',
            'logs' => [
                // - User created ticket several days ago (status = awaiting_agent)
                $this->createTicketLog('ticket_created', '2020-03-16 08:00:00'),
                $this->createTicketLog('message_created', '2020-03-16 08:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ]),
                // - someone just changed status = awaiting_user without any agent reply
                $this->createTicketLog('changed_status', '2020-03-20 13:30:00', [
                    'old_status' => 'awaiting_agent',
                    'new_status' => 'awaiting_user'
                ])
            ],
            'expected' => [
                'total_user_waiting_wh_start' => null,                  // because curr status is 'awaiting_user'
                'total_user_waiting_wh' => 40 * 3600 + 30 * 60,
                'total_to_first_reply_wh' => 42 * 3600                  // 03-16 9:00 - now (there is no agent reply)
            ]
        ]];

        // - User created ticket several days ago  (status = awaiting_agent)
        // - agent replied (status = awaiting_user)
        // - User replied (status = awaiting_agent)
        // - agent note with status = pending
        // - agent replied (status = awaiting_user)
        //
        // Expected calcs:
        // --------------------------------------------------------------
        //  Day     Counted                 Action      Waiting time Sum
        //          Work Time period
        // --------------------------------------------------------------
        //  16      09:00:00 - 18:00:00     08:00 - ticket created  9h
        //  17      09:00:00 - 13:00:00     13:00 - agent reply     13h
        //  18      09:00:00 - 18:00:00     08:00 - user reply      22h
        //  19      09:00:00 - 18:00:00     13:00 - status=pending  31h
        //  20      09:00:00 - 13:00:00     13:00 - agent reply     35h
        //
        $data[] = [[
            'wh' => $this->getStandardWorkHoursSet(),
            'now' => '2020-03-20 15:00:00',
            'logs' => [
                // - User created ticket several days ago  (status = awaiting_agent)
                $this->createTicketLog('ticket_created', '2020-03-16 08:00:00'),
                $this->createTicketLog('message_created', '2020-03-16 08:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ]),
                // - agent replied (status = awaiting_user)
                $this->createTicketLog('message_created', '2020-03-17 13:00:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 0
                ]),
                $this->createTicketLog('changed_status', '2020-03-17 13:00:00', [
                    'old_status' => 'awaiting_agent',
                    'new_status' => 'awaiting_user'
                ]),
                // - User replied (status = awaiting_agent)
                $this->createTicketLog('message_created', '2020-03-18 08:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ]),
                $this->createTicketLog('changed_status', '2020-03-18 08:00:00', [
                    'old_status' => 'awaiting_user',
                    'new_status' => 'awaiting_agent'
                ]),
                // - agent note with status = pending
                $this->createTicketLog('message_created', '2020-03-19 13:00:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 1
                ]),
                $this->createTicketLog('changed_status', '2020-03-19 13:00:00', [
                    'old_status' => 'awaiting_agent',
                    'new_status' => 'pending'
                ]),
                // - agent replied (status = awaiting_user)
                $this->createTicketLog('message_created', '2020-03-20 13:00:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 0
                ]),
                $this->createTicketLog('changed_status', '2020-03-20 13:00:00', [
                    'old_status' => 'pending',
                    'new_status' => 'awaiting_user'
                ]),
            ],
            'expected' => [
                'total_user_waiting_wh_start' => null,                  // because curr status is 'awaiting_user'
                'total_user_waiting_wh' => 35 * 3600,
                'total_to_first_reply_wh' => 13 * 3600                  // 03-16 9:00 - 03-17 13:00
            ]
        ]];

        // - WH has London timezone
        // - User created ticket several days ago
        // - 'now' is NOT during working hours
        //
        // Expected calcs:
        // --------------------------------------------------------------------------
        //  Day     Counted                 Action                  Waiting time Sum
        //          Work Time period
        // --------------------------------------------------------------------------
        //  16      09:00:00 - 18:00:00     08:00 - ticket created  9h
        //
        $data[] = [[
            'wh' => $this->getStandardWorkHoursSet('Europe/London'),
            'now' => '2020-04-13 17:30:00', // 18:30 in London - out of WH
            'logs' => [
                // - User created ticket several days
                $this->createTicketLog('ticket_created', '2020-04-13 12:00:00'), // 13:00 in London
                $this->createTicketLog('message_created', '2020-04-13 12:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ]),
            ],
            'expected' => [
                'total_user_waiting_wh_start' => '2020-04-14 08:00:00', // next day in UTC time (09:00 in London)
                'total_user_waiting_wh' => 5 * 3600,
                'total_to_first_reply_wh' => 5 * 3600
            ]
        ]];


        /**
         * ******************************************************************************************************
         * WORKING HOURS = 00:00 - 23:59 [1,2,3,4,5]
         * - technically we have 1min out of work hours every day (23:59:00 - 00:00:00)
         * - so, there will be -60 sec every day during calculation
         * ******************************************************************************************************
         */

        // - User created ticket several days ago
        // - 'now' is during working hours
        //
        // Expected calcs:
        // ---------------------------------------------------
        //  Day     Counted                 Waiting time Sum
        //          Work Time period
        // ---------------------------------------------------
        //  16      08:00:00 - 23:59:59     16h - 60sec
        //  17      00:00:00 - 23:59:59     40h - 2 * 60sec
        //  18      00:00:00 - 23:59:59     64h - 3 * 60sec
        //  19      00:00:00 - 23:59:59     88h - 4 * 60sec
        //  20      00:00:00 - 13:30:00     101h + 30 min - 4 * 60sec
        //
        $data[] = [[
            'wh' => $this->getFullDayWorkHoursSet(),
            'now' => '2020-03-20 13:30:00',
            'logs' => [
                // - User created ticket several days
                $this->createTicketLog('ticket_created', '2020-03-16 08:00:00'),
                $this->createTicketLog('message_created', '2020-03-16 08:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ]),
                // just a noise
                $this->createTicketLog('message_created', '2020-03-20 13:00:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 1
                ])
            ],
            'expected' => [
                'total_user_waiting_wh_start' => '2020-03-20 13:30:01',     // because 'now' is in working time
                'total_user_waiting_wh' => 101 * 3600 + 30 * 60 - 4 * 60,
                'total_to_first_reply_wh' => 101 * 3600 + 30 * 60 - 4 * 60
            ]
        ]];

        // - User created ticket several days
        // - 'now' is late evening
        //
        // Expected calcs:
        // ---------------------------------------------------
        //  Day     Counted                 Waiting time Sum
        //          Work Time period
        // ---------------------------------------------------
        //  16      08:00:00 - 23:59:59     16h - 60sec
        //  17      00:00:00 - 23:59:59     40h - 2 * 60sec
        //  18      00:00:00 - 23:59:59     64h - 3 * 60sec
        //  19      00:00:00 - 23:59:59     88h - 4 * 60sec
        //  20      00:00:00 - 20:30:00     108h + 30 min - 4 * 60sec
        //
        $data[] = [[
            'wh' => $this->getFullDayWorkHoursSet(),
            'now' => '2020-03-20 20:30:00',
            'logs' => [
                // - User created ticket several days
                $this->createTicketLog('ticket_created', '2020-03-16 08:00:00'),
                $this->createTicketLog('message_created', '2020-03-16 08:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ])
            ],
            'expected' => [
                'total_user_waiting_wh_start' => '2020-03-20 20:30:01', // because 'now' is in working time
                'total_user_waiting_wh' => 108 * 3600 + 30 * 60 - 4 * 60,
                'total_to_first_reply_wh' => 108 * 3600 + 30 * 60 - 4 * 60,
            ]
        ]];

        // - User created ticket several days ago  (status = awaiting_agent)
        // - agent replied (status = awaiting_user)
        //
        // Expected calcs:
        // ---------------------------------------------------
        //  Day     Counted                 Waiting time Sum
        //          Work Time period
        // ---------------------------------------------------
        //  16      08:00:00 - 23:59:59     16h - 60sec
        //  17      00:00:00 - 23:59:59     40h - 2 * 60sec
        //  18      00:00:00 - 23:59:59     64h - 3 * 60sec
        //  19      00:00:00 - 23:59:59     88h - 4 * 60sec
        //  20      00:00:00 - 13:30:00     101h + 30 min - 4 * 60sec
        //
        $data[] = [[
            'wh' => $this->getFullDayWorkHoursSet(),
            'now' => '2020-03-20 15:00:00',
            'logs' => [
                // - User created ticket several days ago  (status = awaiting_agent)
                $this->createTicketLog('ticket_created', '2020-03-16 08:00:00'),
                $this->createTicketLog('message_created', '2020-03-16 08:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ]),
                // - agent replied (status = awaiting_user)
                $this->createTicketLog('message_created', '2020-03-20 13:30:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 0
                ]),
                $this->createTicketLog('changed_status', '2020-03-20 13:30:00', [
                    'old_status' => 'awaiting_agent',
                    'new_status' => 'awaiting_user'
                ]),
                // just a noise
                $this->createTicketLog('message_created', '2020-03-20 14:00:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 1
                ])
            ],
            'expected' => [
                'total_user_waiting_wh_start' => null,                      // because curr status is 'awaiting_user'
                'total_user_waiting_wh' => 101 * 3600 + 30 * 60 - 4 * 60,
                'total_to_first_reply_wh' => 101 * 3600 + 30 * 60 - 4 * 60,
            ]
        ]];

        // - User created ticket several days ago (status = awaiting_agent)
        // - someone just changed status = awaiting_user without any agent reply
        //
        // Expected calcs:
        // ---------------------------------------------------
        //  Day     Counted                 Waiting time Sum
        //          Work Time period
        // ---------------------------------------------------
        //  16      08:00:00 - 23:59:59     16h - 60sec
        //  17      00:00:00 - 23:59:59     40h - 2 * 60sec
        //  18      00:00:00 - 23:59:59     64h - 3 * 60sec
        //  19      00:00:00 - 23:59:59     88h - 4 * 60sec
        //  20      00:00:00 - 13:30:00     101h + 30 min - 4 * 60sec
        //
        $data[] = [[
            'wh' => $this->getFullDayWorkHoursSet(),
            'now' => '2020-03-20 15:00:00',
            'logs' => [
                // - User created ticket several days ago (status = awaiting_agent)
                $this->createTicketLog('ticket_created', '2020-03-16 08:00:00'),
                $this->createTicketLog('message_created', '2020-03-16 08:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ]),
                // - someone just changed status = awaiting_user without any agent reply
                $this->createTicketLog('changed_status', '2020-03-20 13:30:00', [
                    'old_status' => 'awaiting_agent',
                    'new_status' => 'awaiting_user'
                ])
            ],
            'expected' => [
                'total_user_waiting_wh_start' => null,                      // because curr status is 'awaiting_user'
                'total_user_waiting_wh' => 101 * 3600 + 30 * 60 - 4 * 60,
                'total_to_first_reply_wh' => 103 * 3600 - 4 * 60,           // 03-16 8:00 - now (there is no agent reply)
            ]
        ]];

        // - User created ticket several days ago  (status = awaiting_agent)
        // - agent replied (status = awaiting_user)
        // - User replied (status = awaiting_agent)
        // - agent note with status = pending
        // - agent replied (status = awaiting_user)
        //
        // Expected calcs:
        // ---------------------------------------------------
        //  Day     Counted                 Waiting time Sum
        //          Work Time period
        // ---------------------------------------------------
        //  16      08:00:00 - 23:59:59     16h - 60sec
        //  17      00:00:00 - 13:00:00     29h - 60sec
        //  18      08:00:00 - 23:59:59     45h - 2 * 60sec
        //  19      00:00:00 - 23:59:59     69h - 3 * 60sec
        //  20      00:00:00 - 13:00:00     82h - 3 * 60sec
        //
        $data[] = [[
            'wh' => $this->getFullDayWorkHoursSet(),
            'now' => '2020-03-20 15:00:00',
            'logs' => [
                // - User created ticket several days ago  (status = awaiting_agent)
                $this->createTicketLog('ticket_created', '2020-03-16 08:00:00'),
                $this->createTicketLog('message_created', '2020-03-16 08:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ]),
                // - agent replied (status = awaiting_user)
                $this->createTicketLog('message_created', '2020-03-17 13:00:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 0
                ]),
                $this->createTicketLog('changed_status', '2020-03-17 13:00:00', [
                    'old_status' => 'awaiting_agent',
                    'new_status' => 'awaiting_user'
                ]),
                // - User replied (status = awaiting_agent)
                $this->createTicketLog('message_created', '2020-03-18 08:00:00', [
                    'is_agent_message' => 0,
                    'is_agent_note' => 0
                ]),
                $this->createTicketLog('changed_status', '2020-03-18 08:00:00', [
                    'old_status' => 'awaiting_user',
                    'new_status' => 'awaiting_agent'
                ]),
                // - agent note with status = pending
                $this->createTicketLog('message_created', '2020-03-19 13:00:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 1
                ]),
                $this->createTicketLog('changed_status', '2020-03-19 13:00:00', [
                    'old_status' => 'awaiting_agent',
                    'new_status' => 'pending'
                ]),
                // - agent replied (status = awaiting_user)
                $this->createTicketLog('message_created', '2020-03-20 13:00:00', [
                    'is_agent_message' => 1,
                    'is_agent_note' => 0
                ]),
                $this->createTicketLog('changed_status', '2020-03-20 13:00:00', [
                    'old_status' => 'pending',
                    'new_status' => 'awaiting_user'
                ]),
            ],
            'expected' => [
                'total_user_waiting_wh_start' => null,                  // because curr status is 'awaiting_user'
                'total_user_waiting_wh' => 82 * 3600 - 3 * 60,
                'total_to_first_reply_wh' => 29 * 3600 - 60             // 03-16 08:00 - 03-17 13:00
            ]
        ]];

        return $data;
    }


    /**
     * @dataProvider calculateDataProvider
     *
     * @param array $data
     */
    public function testCalculate($data)
    {
        // GIVEN
        $reposMock = m::mock(TicketLogRepos::class);
        $reposMock->shouldReceive('getLogsForTicket')->andReturn($data['logs']);
        $emMock = m::mock(EntityManager::class);
        $emMock->shouldReceive('getRepository')->andReturn($reposMock);

        $calculator = new TicketWhWaitingTimeCalculator($emMock);

        DateTime::setTimestampState((new \DateTime($data['now']))->getTimestamp());

        // WHEN
        $res = [];
        $isOk = $calculator->calculate(new Ticket(), $data['wh'], $res);

        // THEN
        $this->assertTrue($isOk);
        $this->assertEquals(
            $data['expected']['total_user_waiting_wh_start']
                ? new \DateTime($data['expected']['total_user_waiting_wh_start'])
                : null,
            $res['total_user_waiting_wh_start']
        );
        $this->assertEquals($data['expected']['total_user_waiting_wh'],   $res['total_user_waiting_wh']);
        $this->assertEquals($data['expected']['total_to_first_reply_wh'], $res['total_to_first_reply_wh']);
    }

    /**
     *
     * @param string $actionType
     * @param string $dateCreated
     * @param [] $details
     * @return TicketLog
     */
    protected function createTicketLog($actionType, $dateCreated, $details = [])
    {
        $log = new TicketLog();
        $log->action_type = $actionType;
        $log->date_created = new \DateTime($dateCreated);
        $log->details = $details;

        return $log;
    }

    /**
     *
     * @return WorkHoursSet
     */
    protected function getStandardWorkHoursSet($timezone = 'UTC')
    {
        return new WorkHoursSet(
            9 * 3600,
            18 * 3600,
            [1, 2, 3, 4, 5],
            $timezone,
            []
        );
    }

    /**
     *
     * @return WorkHoursSet
     */
    protected function getFullDayWorkHoursSet()
    {
        return new WorkHoursSet(
            0,
            23 * 3600 + 59 * 60,
            [1, 2, 3, 4, 5],
            'UTC',
            []
        );
    }
}
