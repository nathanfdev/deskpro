<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Tickets\Actions\AddAgentReply;
use DpTestingMocks\ContainerMock;
use Mockery as m;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

class AddAgentReplyTest extends \DpUnitTestCase
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;

	/**
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private function getMockContainer()
	{
		if ($this->container) return $this->container;

		$this->container = ContainerMock::create()
			->withAgentData()
			->withNullEm()
			->get();

		return $this->container;
	}

	public function testAdd()
	{
		$ticket = new Ticket();
		$exec   = new ExecutorContext();

		$tok = sha1(microtime(true) . mt_rand(10000,99999));
		$action = new AddAgentReply(array(
			'by_assigned_agent' => true,
			'by_agent_id' => 1,
			'reply_text' => 'Test reply ' . $tok
		));
		$action->setContainer($this->getMockContainer());
		$action->applyAction($ticket, $exec);

		$this->assertEquals(1, count($ticket->messages));
		$this->assertContains($tok, $ticket->messages[0]->message);
		$this->assertNotNull($ticket->messages[0]->person);
		$this->assertEquals(1, $ticket->messages[0]->person->id);
	}
}