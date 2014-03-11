<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Tickets\Actions\ModMuteAgentEmails;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

class ModMuteAgentEmailsTest extends \DpUnitTestCase
{
	public function testAdd()
	{
		$ticket = new Ticket();
		$exec   = new ExecutorContext();

		$action = new ModMuteAgentEmails();
		$action->applyAction($ticket, $exec);

		$this->assertTrue($exec->getVars()->get('mute_agent_emails'));
	}
}