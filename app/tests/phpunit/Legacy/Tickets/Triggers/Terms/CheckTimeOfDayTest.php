<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckTimeOfDay;
use DpTest\DeskProTestCase;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckTimeOfDayTest extends DeskProTestCase
{
    public function testBefore()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $now = \DateTime::createFromFormat('Y-m-d H:i:s', '2014-03-10 15:00:00', new \DateTimeZone('UTC'));

        $check = new CheckTimeOfDay('lt', array('time1' => '18:00', 'test_date' => $now, 'tz' => 'UTC'));
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckTimeOfDay('lt', array('time1' => '14:30', 'test_date' => $now, 'tz' => 'UTC'));
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testAfter()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $now = \DateTime::createFromFormat('Y-m-d H:i:s', '2014-03-10 15:00:00', new \DateTimeZone('UTC'));

        $check = new CheckTimeOfDay('gt', array('time1' => '14:00', 'test_date' => $now, 'tz' => 'UTC'));
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckTimeOfDay('gt', array('time1' => '18:00', 'test_date' => $now, 'tz' => 'UTC'));
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }

    public function testBetween()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $now = \DateTime::createFromFormat('Y-m-d H:i:s', '2014-03-10 15:00:00', new \DateTimeZone('UTC'));

        $check = new CheckTimeOfDay(
            'between',
            array('time1' => '14:00', 'time2' => '18:00', 'test_date' => $now, 'tz' => 'UTC')
        );
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckTimeOfDay(
            'between',
            array('time1' => '14:00', 'time2' => '15:00', 'test_date' => $now, 'tz' => 'UTC')
        );
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckTimeOfDay(
            'between',
            array('time1' => '15:00', 'time2' => '16:00', 'test_date' => $now, 'tz' => 'UTC')
        );
        $this->assertTrue($check->isTriggerMatch($ticket, $exec));

        $check = new CheckTimeOfDay(
            'between',
            array('time1' => '04:00', 'time2' => '08:00', 'test_date' => $now, 'tz' => 'UTC')
        );
        $this->assertFalse($check->isTriggerMatch($ticket, $exec));
    }
}
