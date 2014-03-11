<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\ModSetUserVar;
use Application\DeskPRO\Tickets\ExecutorContext;

class ModSetUserVarTest extends \DpUnitTestCase
{
	public function testSet()
	{
		$ticket = new Ticket();
		$exec   = new ExecutorContext();

		$action = new ModSetUserVar(array('name' => 'testvar', 'value' => 'test123'));
		$action->applyAction($ticket, $exec);

		$this->assertTrue($exec->getVars()->has('uservar_testvar'));
		$this->assertEquals('test123', $exec->getVars()->get('uservar_testvar'));
	}

	public function testNotSet()
	{
		$ticket = new Ticket();
		$exec   = new ExecutorContext();

		$action = new ModSetUserVar(array('name' => 'xxx', 'value' => 'test123'));
		$action->applyAction($ticket, $exec);

		$this->assertFalse($exec->getVars()->has('uservar_testvar'));
	}
}