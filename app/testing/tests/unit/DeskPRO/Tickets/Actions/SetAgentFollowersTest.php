<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Tickets\Actions\SetAgent;
use Mockery as m;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

class SetAgentTest extends \DpUnitTestCase
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

		$agent_data = m::mock('Application\\DeskPRO\\DependencyInjection\\SystemServices\\AgentDataService');
		$agent_data->shouldReceive('get')->andReturnUsing(function($id) {
			if ($id == 200) {
				return null;
			}
			$agent = new Person();
			$agent->id = $id;
			return $agent;
		});

		$container = m::mock('Application\\DeskPRO\\DependencyInjection\\DeskproContainer');
		$container->shouldReceive('getAgentData')->andReturn($agent_data);

		$this->container = $container;
		return $this->container;
	}

	public function testAdd()
	{
		$ticket = new Ticket();
		$exec   = new ExecutorContext();

		$action = new SetAgent(array('agent_id' => 55));
		$action->setContainer($this->getMockContainer());

		$action->applyAction($ticket, $exec);

		$this->assertInstanceOf('Application\\DeskPRO\\Entity\\Person', $ticket->agent);
		$this->assertEquals(55, $ticket->agent->id);
	}
}