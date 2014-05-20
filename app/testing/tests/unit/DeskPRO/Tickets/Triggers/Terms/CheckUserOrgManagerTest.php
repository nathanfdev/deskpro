<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckUserOrgManager;

require_once 'AbstractStringCheckTest.php';

class CheckUserOrgManagerTest extends \DpUnitTestCase
{
	public function testUserIsManager()
	{
		$ticket = new Ticket();

		$org = new Organization();

		$person = new Person();
		$person->organization = $org;
		$person->organization_manager = true;
		$ticket->person = $person;

		$exec = new ExecutorContext();

		$check = new CheckUserOrgManager('is');
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));
	}

	public function testUserNotManager()
	{
		$ticket = new Ticket();

		$person = new Person();
		$ticket->person = $person;

		$exec = new ExecutorContext();

		$check = new CheckUserOrgManager('not');
		$this->assertTrue($check->isTriggerMatch($ticket, $exec));
	}
}