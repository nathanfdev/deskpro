<?php

namespace DpTest\DeskPRO\Application\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Tickets\Slas\SlaCalculator;
use Application\DeskPRO\Entity\Ticket;
use Orb\Util\WorkHoursSet;
use Orb\Util\WorkHoursSetAll;
use Orb\Util\TimeUnit;
use Orb\Util\Testable\DateTime;
use DpTest\PortalTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;

class SlaCalculatorTest extends PortalTestCase
{
    /**
     * @var DeskproContainer
     */
    protected $containerBefore;

    public function setUp()
    {
        $this->containerBefore = App::$container;
        DateTime::unsetTimestampState();
    }

    public function tearDown()
    {
        App::$container = $this->containerBefore;
        DateTime::unsetTimestampState();
    }

    public function calculateDataProvider()
    {
        $data = [];

        // FIRST_RESPONSE and has excluded statuses
        //
        // - WH: standard
        // - Sla: FIRST_RESPONSE, warn: 25h, fail: 30h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: `pending`
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 17:00:00     awaiting_agent      8h      8h
        //  22      17:00:00 - 18:00:00     pending             1h      8h
        //  23      09:00:00 - 12:00:00     pending             3h      8h
        //  23      12:00:00 - 15:00:00     awaiting_user       3h      11h
        //  23      15:00:00 - 18:00:00     awaiting_agent      3h      14h                     16:00
        //  24      09:00:00 - 17:00:00     awaiting_agent      8h      22h
        //  25                                                                  11:00   16:00
        //
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => $this->getStandardWorkHoursSet(),
                'type' => SlaCalculator::TYPE_FIRST_RESPONSE,
                'warn_time' => new TimeUnit(25, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(30, TimeUnit::HOURS),
                'excluded_statuses' => ['pending'],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-23 15:00:00',
                'waiting_times' => [
                    [
                        'start' => strtotime('2020-06-22 09:00:00'),
                        'end' => strtotime('2020-06-22 17:00:00'),
                        'ticket_status' => 'awaiting_agent'
                    ],
                    [
                        'start' => strtotime('2020-06-22 17:00:00'),
                        'end' => strtotime('2020-06-23 12:00:00'),
                        'ticket_status' => 'pending'
                    ],
                    [
                        'start' => strtotime('2020-06-23 12:00:00'),
                        'end' => strtotime('2020-06-23 15:00:00'),
                        'ticket_status' => 'awaiting_user'
                    ]
                ],
            ],
            'time_until_date' => '2020-06-23 16:00:00',
            'expected' => [
                'warn_date' => '2020-06-25 11:00:00',
                'fail_date' => '2020-06-25 16:00:00',
                'complete_date' => null,
                'time_until' => 12 * 60 * 60
            ]
        ]];

        // FIRST_RESPONSE and doesn't have excluded statuses
        //
        // - WH: standard
        // - Sla: FIRST_RESPONSE, warn: 25h, fail: 30h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: NONE
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 17:00:00     awaiting_agent      8h      8h
        //  22      17:00:00 - 18:00:00     pending             1h      9h
        //  23      09:00:00 - 12:00:00     pending             3h      12h
        //  23      12:00:00 - 15:00:00     awaiting_user       3h      15h
        //  23      15:00:00 - 18:00:00     awaiting_agent      3h      18h                     16:00
        //  24      09:00:00 - 17:00:00     awaiting_agent      8h      26h     16:00
        //  25                                                                          12:00
        //
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => $this->getStandardWorkHoursSet(),
                'type' => SlaCalculator::TYPE_FIRST_RESPONSE,
                'warn_time' => new TimeUnit(25, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(30, TimeUnit::HOURS),
                'excluded_statuses' => [],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-23 15:00:00',
                'waiting_times' => [
                    [
                        'start' => strtotime('2020-06-22 09:00:00'),
                        'end' => strtotime('2020-06-22 17:00:00'),
                        'ticket_status' => 'awaiting_agent'
                    ],
                    [
                        'start' => strtotime('2020-06-22 17:00:00'),
                        'end' => strtotime('2020-06-23 12:00:00'),
                        'ticket_status' => 'pending'
                    ],
                    [
                        'start' => strtotime('2020-06-23 12:00:00'),
                        'end' => strtotime('2020-06-23 15:00:00'),
                        'ticket_status' => 'awaiting_user'
                    ]
                ],
            ],
            'time_until_date' => '2020-06-23 16:00:00',
            'expected' => [
                'warn_date' => '2020-06-24 16:00:00',
                'fail_date' => '2020-06-25 12:00:00',
                'complete_date' => null,
                'time_until' => 16 * 60 * 60
            ]
        ]];

        // FIRST_RESPONSE and has excluded statuses and WH 24x7
        //
        // - WH: 24x7
        // - Sla: FIRST_RESPONSE, warn: 40h, fail: 45h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: `pending`
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 17:00:00     awaiting_agent      8h      8h
        //  22      17:00:00 - 24:00:00     pending             7h      8h
        //  23      00:00:00 - 12:00:00     pending             12h     8h
        //  23      12:00:00 - 15:00:00     awaiting_user       3h      11h
        //  23      15:00:00 - 24:00:00     awaiting_agent      9h      20h                     16:00
        //  24      00:00:00 - 17:00:00     awaiting_agent      17h     37h     20:00
        //  25                                                                         01:00
        //
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => new WorkHoursSetAll(),
                'type' => SlaCalculator::TYPE_FIRST_RESPONSE,
                'warn_time' => new TimeUnit(40, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(45, TimeUnit::HOURS),
                'excluded_statuses' => ['pending'],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-23 15:00:00',
                'waiting_times' => [
                    [
                        'start' => strtotime('2020-06-22 09:00:00'),
                        'end' => strtotime('2020-06-22 17:00:00'),
                        'ticket_status' => 'awaiting_agent'
                    ],
                    [
                        'start' => strtotime('2020-06-22 17:00:00'),
                        'end' => strtotime('2020-06-23 12:00:00'),
                        'ticket_status' => 'pending'
                    ],
                    [
                        'start' => strtotime('2020-06-23 12:00:00'),
                        'end' => strtotime('2020-06-23 15:00:00'),
                        'ticket_status' => 'awaiting_user'
                    ]
                ],
            ],
            'time_until_date' => '2020-06-23 16:00:00',
            'expected' => [
                'warn_date' => '2020-06-24 20:00:00',
                'fail_date' => '2020-06-25 01:00:00',
                'complete_date' => null,
                'time_until' => 12 * 60 * 60
            ]
        ]];

        // FIRST_RESPONSE and has excluded statuses and past warn/fail dates
        //
        // - WH: standard
        // - Sla: FIRST_RESPONSE, warn: 7h, fail: 10h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: `pending`
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 17:00:00     awaiting_agent      8h      8h      16:00
        //  22      17:00:00 - 18:00:00     pending             1h      8h                      17:30
        //  23      09:00:00 - 12:00:00     pending             3h      8h
        //  23      12:00:00 - 15:00:00     awaiting_user       3h      11h             14:00
        //  23      15:00:00 - 18:00:00     awaiting_agent      3h      14h                     
        //  24      09:00:00 - 17:00:00     awaiting_agent      8h      22h
        //
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => $this->getStandardWorkHoursSet(),
                'type' => SlaCalculator::TYPE_FIRST_RESPONSE,
                'warn_time' => new TimeUnit(7, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(10, TimeUnit::HOURS),
                'excluded_statuses' => ['pending'],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-23 15:00:00',
                'waiting_times' => [
                    [
                        'start' => strtotime('2020-06-22 09:00:00'),
                        'end' => strtotime('2020-06-22 17:00:00'),
                        'ticket_status' => 'awaiting_agent'
                    ],
                    [
                        'start' => strtotime('2020-06-22 17:00:00'),
                        'end' => strtotime('2020-06-23 12:00:00'),
                        'ticket_status' => 'pending'
                    ],
                    [
                        'start' => strtotime('2020-06-23 12:00:00'),
                        'end' => strtotime('2020-06-23 15:00:00'),
                        'ticket_status' => 'awaiting_user'
                    ]
                ],
            ],
            'time_until_date' => '2020-06-22 17:30:00',
            'expected' => [
                'warn_date' => '2020-06-22 16:00:00',
                'fail_date' => '2020-06-23 14:00:00',
                'complete_date' => null,
                'time_until' => 8 * 60 * 60
            ]
        ]];

        // FIRST_RESPONSE and has excluded statuses and no ticket->waiting_times
        //
        // - WH: standard
        // - Sla: FIRST_RESPONSE, warn: 25h, fail: 30h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: `pending`
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 18:00:00     awaiting_agent      9h      9h
        //  23      09:00:00 - 18:00:00     awaiting_agent      18h     18h                     16:00
        //  24      09:00:00 - 17:00:00     awaiting_agent      26h     26h     16:00
        //  25                                                                          12:00
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => $this->getStandardWorkHoursSet(),
                'type' => SlaCalculator::TYPE_FIRST_RESPONSE,
                'warn_time' => new TimeUnit(25, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(30, TimeUnit::HOURS),
                'excluded_statuses' => ['pending'],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-22 09:00:00',
                'waiting_times' => [
                ],
            ],
            'time_until_date' => '2020-06-23 16:00:00',
            'expected' => [
                'warn_date' => '2020-06-24 16:00:00',
                'fail_date' => '2020-06-25 12:00:00',
                'complete_date' => null,
                'time_until' => 16 * 60 * 60
            ]
        ]];

        // FIRST_RESPONSE and has excluded statuses and ticket->waiting_times has old format
        //
        // - WH: standard
        // - Sla: FIRST_RESPONSE, warn: 25h, fail: 30h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: `pending`
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 17:00:00     awaiting_agent      8h      8h
        //  22      17:00:00 - 18:00:00     pending             1h      9h
        //  23      09:00:00 - 12:00:00     pending             3h      12h
        //  23      12:00:00 - 15:00:00     awaiting_user       3h      15h
        //  23      15:00:00 - 18:00:00     awaiting_agent      3h      18h                     16:00
        //  24      09:00:00 - 17:00:00     awaiting_agent      8h      26h     16:00
        //  25                                                                          12:00
        //
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => $this->getStandardWorkHoursSet(),
                'type' => SlaCalculator::TYPE_FIRST_RESPONSE,
                'warn_time' => new TimeUnit(25, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(30, TimeUnit::HOURS),
                'excluded_statuses' => ['pending'],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-23 15:00:00',
                'waiting_times' => [
                    [
                        'start' => strtotime('2020-06-22 09:00:00'),
                        'end' => strtotime('2020-06-22 17:00:00'),
                        'type' => 'user'
                    ],
                    [
                        'start' => strtotime('2020-06-22 17:00:00'),
                        'end' => strtotime('2020-06-23 12:00:00'),
                        'type' => 'user' // although this is pending status we will count it because of old format
                    ],
                    [
                        'start' => strtotime('2020-06-23 12:00:00'),
                        'end' => strtotime('2020-06-23 15:00:00'),
                        'ticket_status' => 'awaiting_user'
                    ]
                ],
            ],
            'time_until_date' => '2020-06-23 16:00:00',
            'expected' => [
                'warn_date' => '2020-06-24 16:00:00',
                'fail_date' => '2020-06-25 12:00:00',
                'complete_date' => null,
                'time_until' => 16 * 60 * 60
            ]
        ]];

        // RESOLUTION and has excluded statuses
        //
        // - WH: standard
        // - Sla: RESOLUTION, warn: 25h, fail: 30h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: `pending`
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 17:00:00     awaiting_agent      8h      8h
        //  22      17:00:00 - 18:00:00     pending             1h      8h
        //  23      09:00:00 - 12:00:00     pending             3h      8h
        //  23      12:00:00 - 15:00:00     awaiting_user       3h      11h
        //  23      15:00:00 - 18:00:00     awaiting_agent      3h      14h                     16:00
        //  24      09:00:00 - 17:00:00     awaiting_agent      8h      22h
        //  25                                                                  11:00   16:00
        //
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => $this->getStandardWorkHoursSet(),
                'type' => SlaCalculator::TYPE_RESOLUTION,
                'warn_time' => new TimeUnit(25, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(30, TimeUnit::HOURS),
                'excluded_statuses' => ['pending'],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-23 15:00:00',
                'waiting_times' => [
                    [
                        'start' => strtotime('2020-06-22 09:00:00'),
                        'end' => strtotime('2020-06-22 17:00:00'),
                        'ticket_status' => 'awaiting_agent'
                    ],
                    [
                        'start' => strtotime('2020-06-22 17:00:00'),
                        'end' => strtotime('2020-06-23 12:00:00'),
                        'ticket_status' => 'pending'
                    ],
                    [
                        'start' => strtotime('2020-06-23 12:00:00'),
                        'end' => strtotime('2020-06-23 15:00:00'),
                        'ticket_status' => 'awaiting_user'
                    ]
                ],
            ],
            'time_until_date' => '2020-06-23 16:00:00',
            'expected' => [
                'warn_date' => '2020-06-25 11:00:00',
                'fail_date' => '2020-06-25 16:00:00',
                'complete_date' => null,
                'time_until' => 12 * 60 * 60
            ]
        ]];

        // RESOLUTION and doesn't have excluded statuses
        //
        // - WH: standard
        // - Sla: FIRST_RESPONSE, warn: 25h, fail: 30h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: NONE
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 17:00:00     awaiting_agent      8h      8h
        //  22      17:00:00 - 18:00:00     pending             1h      9h
        //  23      09:00:00 - 12:00:00     pending             3h      12h
        //  23      12:00:00 - 15:00:00     awaiting_user       3h      15h
        //  23      15:00:00 - 18:00:00     awaiting_agent      3h      18h                     16:00
        //  24      09:00:00 - 17:00:00     awaiting_agent      8h      26h     16:00
        //  25                                                                          12:00
        //
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => $this->getStandardWorkHoursSet(),
                'type' => SlaCalculator::TYPE_RESOLUTION,
                'warn_time' => new TimeUnit(25, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(30, TimeUnit::HOURS),
                'excluded_statuses' => [],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-23 15:00:00',
                'waiting_times' => [
                    [
                        'start' => strtotime('2020-06-22 09:00:00'),
                        'end' => strtotime('2020-06-22 17:00:00'),
                        'ticket_status' => 'awaiting_agent'
                    ],
                    [
                        'start' => strtotime('2020-06-22 17:00:00'),
                        'end' => strtotime('2020-06-23 12:00:00'),
                        'ticket_status' => 'pending'
                    ],
                    [
                        'start' => strtotime('2020-06-23 12:00:00'),
                        'end' => strtotime('2020-06-23 15:00:00'),
                        'ticket_status' => 'awaiting_user'
                    ]
                ],
            ],
            'time_until_date' => '2020-06-23 16:00:00',
            'expected' => [
                'warn_date' => '2020-06-24 16:00:00',
                'fail_date' => '2020-06-25 12:00:00',
                'complete_date' => null,
                'time_until' => 16 * 60 * 60
            ]
        ]];

        // WAITING_TIME and has excluded statuses
        //
        // - WH: standard
        // - Sla: WAITING_TIME, warn: 25h, fail: 30h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: `pending`
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 17:00:00     awaiting_agent      8h      8h
        //  22      17:00:00 - 18:00:00     pending             1h      8h
        //  23      09:00:00 - 12:00:00     pending             3h      8h
        //  23      12:00:00 - 15:00:00     awaiting_user       3h      8h
        //  23      15:00:00 - 18:00:00     awaiting_agent      3h      11h                     16:00
        //  24      09:00:00 - 17:00:00     awaiting_agent      8h      19h
        //  25                                                                  14:00
        //  26                                                                          10:00
        //
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => $this->getStandardWorkHoursSet(),
                'type' => SlaCalculator::TYPE_WAITING_TIME,
                'warn_time' => new TimeUnit(25, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(30, TimeUnit::HOURS),
                'excluded_statuses' => ['pending'],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-23 15:00:00',
                'waiting_times' => [
                    [
                        'start' => strtotime('2020-06-22 09:00:00'),
                        'end' => strtotime('2020-06-22 17:00:00'),
                        'ticket_status' => 'awaiting_agent'
                    ],
                    [
                        'start' => strtotime('2020-06-22 17:00:00'),
                        'end' => strtotime('2020-06-23 12:00:00'),
                        'ticket_status' => 'pending'
                    ],
                    [
                        'start' => strtotime('2020-06-23 12:00:00'),
                        'end' => strtotime('2020-06-23 15:00:00'),
                        'ticket_status' => 'awaiting_user'
                    ]
                ],
            ],
            'time_until_date' => '2020-06-23 16:00:00',
            'expected' => [
                'warn_date' => '2020-06-25 14:00:00',
                'fail_date' => '2020-06-26 10:00:00',
                'complete_date' => null,
                'time_until' => 8 * 60 * 60
            ]
        ]];

        // WAITING_TIME and doesn't have excluded statuses
        //
        // - WH: standard
        // - Sla: WAITING_TIME, warn: 25h, fail: 30h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: NONE
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 17:00:00     awaiting_agent      8h      8h
        //  22      17:00:00 - 18:00:00     pending             1h      9h
        //  23      09:00:00 - 12:00:00     pending             3h      12h
        //  23      12:00:00 - 15:00:00     awaiting_user       3h      12h
        //  23      15:00:00 - 18:00:00     awaiting_agent      3h      15h                     16:00
        //  24      09:00:00 - 17:00:00     awaiting_agent      8h      23h
        //  25                                                                  10:00   15:00
        //
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => $this->getStandardWorkHoursSet(),
                'type' => SlaCalculator::TYPE_WAITING_TIME,
                'warn_time' => new TimeUnit(25, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(30, TimeUnit::HOURS),
                'excluded_statuses' => [],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-23 15:00:00',
                'waiting_times' => [
                    [
                        'start' => strtotime('2020-06-22 09:00:00'),
                        'end' => strtotime('2020-06-22 17:00:00'),
                        'ticket_status' => 'awaiting_agent'
                    ],
                    [
                        'start' => strtotime('2020-06-22 17:00:00'),
                        'end' => strtotime('2020-06-23 12:00:00'),
                        'ticket_status' => 'pending'
                    ],
                    [
                        'start' => strtotime('2020-06-23 12:00:00'),
                        'end' => strtotime('2020-06-23 15:00:00'),
                        'ticket_status' => 'awaiting_user'
                    ]
                ],
            ],
            'time_until_date' => '2020-06-23 16:00:00',
            'expected' => [
                'warn_date' => '2020-06-25 10:00:00',
                'fail_date' => '2020-06-25 15:00:00',
                'complete_date' => null,
                'time_until' => 12 * 60 * 60
            ]
        ]];

        // WAITING_TIME and has excluded statuses and WH 24x7
        //
        // - WH: 24x7
        // - Sla: WAITING_TIME, warn: 35h, fail: 40h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: `pending`
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 17:00:00     awaiting_agent      8h      8h
        //  22      17:00:00 - 24:00:00     pending             7h      8h
        //  23      00:00:00 - 12:00:00     pending             12h     8h
        //  23      12:00:00 - 15:00:00     awaiting_user       3h      8h
        //  23      15:00:00 - 24:00:00     awaiting_agent      9h      17h                     16:00
        //  24      00:00:00 - 17:00:00     awaiting_agent      17h     34h     18:00   23:00
        //
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => new WorkHoursSetAll(),
                'type' => SlaCalculator::TYPE_WAITING_TIME,
                'warn_time' => new TimeUnit(35, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(40, TimeUnit::HOURS),
                'excluded_statuses' => ['pending'],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-23 15:00:00',
                'waiting_times' => [
                    [
                        'start' => strtotime('2020-06-22 09:00:00'),
                        'end' => strtotime('2020-06-22 17:00:00'),
                        'ticket_status' => 'awaiting_agent'
                    ],
                    [
                        'start' => strtotime('2020-06-22 17:00:00'),
                        'end' => strtotime('2020-06-23 12:00:00'),
                        'ticket_status' => 'pending'
                    ],
                    [
                        'start' => strtotime('2020-06-23 12:00:00'),
                        'end' => strtotime('2020-06-23 15:00:00'),
                        'ticket_status' => 'awaiting_user'
                    ]
                ],
            ],
            'time_until_date' => '2020-06-23 16:00:00',
            'expected' => [
                'warn_date' => '2020-06-24 18:00:00',
                'fail_date' => '2020-06-24 23:00:00',
                'complete_date' => null,
                'time_until' => 8 * 60 * 60
            ]
        ]];

        // WAITING_TIME and has excluded statuses and no ticket->waiting_times
        //
        // - WH: standard
        // - Sla: WAITING_TIME, warn: 25h, fail: 30h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: `pending`
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 18:00:00     awaiting_agent      9h      9h
        //  23      09:00:00 - 18:00:00     awaiting_agent      18h     18h                     16:00
        //  24      09:00:00 - 17:00:00     awaiting_agent      26h     26h     16:00
        //  25                                                                          12:00
        //
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => $this->getStandardWorkHoursSet(),
                'type' => SlaCalculator::TYPE_WAITING_TIME,
                'warn_time' => new TimeUnit(25, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(30, TimeUnit::HOURS),
                'excluded_statuses' => ['pending'],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-22 09:00:00',
                'waiting_times' => [
                ],
            ],
            'time_until_date' => '2020-06-23 16:00:00',
            'expected' => [
                'warn_date' => '2020-06-24 16:00:00',
                'fail_date' => '2020-06-25 12:00:00',
                'complete_date' => null,
                'time_until' => 0
            ]
        ]];

        // WAITING_TIME and has excluded statuses and ticket->waiting_times has old format
        //
        // - WH: standard
        // - Sla: WAITING_TIME, warn: 25h, fail: 30h
        // - ticket status: 'awaiting_agent'
        // - excluded statuses: `pending`
        // - now: 2020-06-24 17:00
        //
        // Expected calcs:
        // -------------------------------------------------------------------------------------------------------
        //  Day     Status                  Status              Work    Counted Warn    Fail    Time
        //          Work Time period                            time    time    point   point   until
        //                                                      spent   sum
        // -------------------------------------------------------------------------------------------------------
        //  22      09:00:00 - 17:00:00     awaiting_agent      8h      8h
        //  22      17:00:00 - 18:00:00     pending             1h      9h
        //  23      09:00:00 - 12:00:00     pending             3h      12h
        //  23      12:00:00 - 15:00:00     awaiting_user       3h      12h
        //  23      15:00:00 - 18:00:00     awaiting_agent      3h      15h                     16:00
        //  24      09:00:00 - 17:00:00     awaiting_agent      8h      23h
        //  25                                                                  10:00   15:00
        //
        $data[] = [[
            'now' => '2020-06-24 17:00:00',
            'sla' => [
                'wh' => $this->getStandardWorkHoursSet(),
                'type' => SlaCalculator::TYPE_WAITING_TIME,
                'warn_time' => new TimeUnit(25, TimeUnit::HOURS),
                'fail_time' => new TimeUnit(30, TimeUnit::HOURS),
                'excluded_statuses' => ['pending'],
            ],
            'ticket' => [
                'date_created' => '2020-06-22 09:00:00',
                'status' => 'awaiting_agent',
                'date_status' => '2020-06-23 15:00:00',
                'waiting_times' => [
                    [
                        'start' => strtotime('2020-06-22 09:00:00'),
                        'end' => strtotime('2020-06-22 17:00:00'),
                        'type' => 'user'
                    ],
                    [
                        'start' => strtotime('2020-06-22 17:00:00'),
                        'end' => strtotime('2020-06-23 12:00:00'),
                        'type' => 'user' // although this is pending status we will count it because of old format
                    ],
                    [
                        'start' => strtotime('2020-06-23 12:00:00'),
                        'end' => strtotime('2020-06-23 15:00:00'),
                        'ticket_status' => 'awaiting_user'
                    ]
                ],
            ],
            'time_until_date' => '2020-06-23 16:00:00',
            'expected' => [
                'warn_date' => '2020-06-25 10:00:00',
                'fail_date' => '2020-06-25 15:00:00',
                'complete_date' => null,
                'time_until' => 12 * 60 * 60
            ]
        ]];

        return  $data;
    }

    /**
     * @dataProvider calculateDataProvider
     *
     * @param array $data
     */
    public function testSla($data)
    {
        // GIVEN
        App::$container = ContainerMock::create()
            ->withNullEm()
            ->withBaseTicketStatusesMock()
            ->get();

        $sla = new SlaCalculator(
            $data['sla']['type'],
            $data['sla']['wh'],
            $data['sla']['warn_time'],
            $data['sla']['fail_time'],
            $data['sla']['excluded_statuses'],
        );

        $ticket = new Ticket();
        $ticket->date_created = new \DateTime($data['ticket']['date_created']);
        $ticket->waiting_times = $data['ticket']['waiting_times'];
        $ticket->status = $data['ticket']['status'];
        $ticket->date_status = new \DateTime($data['ticket']['date_status']);

        DateTime::setTimestampState((new \DateTime($data['now']))->getTimestamp());

        // WHEN / THEN
        $this->assertEquals(
            $data['expected']['warn_date'] ? new \DateTime($data['expected']['warn_date']) : null,
            $sla->calculateWarnDate($ticket),
            "Wrong warn date"
        );
        $this->assertEquals(
            $data['expected']['fail_date'] ? new \DateTime($data['expected']['fail_date']) : null,
            $sla->calculateFailDate($ticket),
            "Wrong fail date"
        );
        $this->assertEquals(
            $data['expected']['complete_date'] ? new \DateTime($data['expected']['complete_date']) : null,
            $sla->calculateCompletedDate($ticket),
            "Wrong complete date"
        );
        $this->assertEquals(
            $data['expected']['time_until'],
            $sla->calculateTimeUntil($ticket, new \DateTime($data['time_until_date'])),
            "Wrong time until date"
        );
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
