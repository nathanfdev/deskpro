<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckTimeOfDay;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckTimeOfDayTest extends \DpUnitTestCase
{
	public function testBefore()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$now = \DateTime::createFromFormat('Y-m-d H:i:s', '2014-03-10 15:00:00', new \DateTimeZone('UTC'));

		$check = new CheckTimeOfDay('lt', array('time1' => '18:00', 'test_date' => $now, 'tz' => 'UTC'));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));

		$check = new CheckTimeOfDay('lt', array('time1' => '14:30', 'test_date' => $now, 'tz' => 'UTC'));
		$this->assertFalse($check->isTriggerMatch($ticket, $exec));
	}

	public function testAfter()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$now = \DateTime::createFromFormat('Y-m-d H:i:s', '2014-03-10 15:00:00', new \DateTimeZone('UTC'));

		$check = new CheckTimeOfDay('gt', array('time1' => '14:00', 'test_date' => $now, 'tz' => 'UTC'));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));

		$check = new CheckTimeOfDay('gt', array('time1' => '18:00', 'test_date' => $now, 'tz' => 'UTC'));
		$this->assertFalse($check->isTriggerMatch($ticket, $exec));
	}

	public function testBetween()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$now = \DateTime::createFromFormat('Y-m-d H:i:s', '2014-03-10 15:00:00', new \DateTimeZone('UTC'));

		$check = new CheckTimeOfDay('between', array('time1' => '14:00', 'time2' => '18:00', 'test_date' => $now, 'tz' => 'UTC'));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));

		$check = new CheckTimeOfDay('between', array('time1' => '14:00', 'time2' => '15:00', 'test_date' => $now, 'tz' => 'UTC'));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));

		$check = new CheckTimeOfDay('between', array('time1' => '15:00', 'time2' => '16:00', 'test_date' => $now, 'tz' => 'UTC'));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));

		$check = new CheckTimeOfDay('between', array('time1' => '04:00', 'time2' => '08:00', 'test_date' => $now, 'tz' => 'UTC'));
		$this->assertFalse($check->isTriggerMatch($ticket, $exec));
	}
}