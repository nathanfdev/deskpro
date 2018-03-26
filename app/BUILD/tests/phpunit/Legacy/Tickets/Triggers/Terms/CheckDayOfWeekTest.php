<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckDayOfWeek;
use DpTest\DeskProTestCase;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckDayOfWeekTest extends DeskProTestCase
{
    public function testInSingleDow()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        // Monday
        $now = \DateTime::createFromFormat('Y-m-d H:i:s', '2014-03-10 15:00:00', new \DateTimeZone('UTC'));

        $check = new CheckDayOfWeek('is', ['days' => [1], 'test_date' => $now, 'tz' => 'UTC']);
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckDayOfWeek('is', ['days' => [5], 'test_date' => $now, 'tz' => 'UTC']);
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testInMultDow()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        // Wednesday
        $now = \DateTime::createFromFormat('Y-m-d H:i:s', '2014-03-12 15:00:00', new \DateTimeZone('UTC'));

        $check = new CheckDayOfWeek('is', ['days' => [1, 3, 7], 'test_date' => $now, 'tz' => 'UTC']);
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckDayOfWeek('is', ['days' => [1, 7], 'test_date' => $now, 'tz' => 'UTC']);
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }
}
