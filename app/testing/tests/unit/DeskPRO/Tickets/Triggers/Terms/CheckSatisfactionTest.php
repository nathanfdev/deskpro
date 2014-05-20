<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckSatisfaction;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckStatus;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckSatisfactionTest extends \DpUnitTestCase
{
	public function testPositive()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$ticket->date_feedback_rating = new \DateTime();
		$ticket->feedback_rating = 1;

		$check = new CheckSatisfaction('is', array('rating' => 1));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));
	}

	public function testNegative()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$ticket->date_feedback_rating = new \DateTime();
		$ticket->feedback_rating = -1;

		$check = new CheckSatisfaction('is', array('rating' => -1));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));
	}

	public function testNeutral()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$ticket->date_feedback_rating = new \DateTime();
		$ticket->feedback_rating = 0;

		$check = new CheckSatisfaction('is', array('rating' => 0));
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));
	}

	public function testIsset()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$ticket->date_feedback_rating = new \DateTime();
		$ticket->feedback_rating = 0;

		$check = new CheckSatisfaction('isset');
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));
	}

	public function testNotIsset()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$check = new CheckSatisfaction('notisset');
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));
	}
}