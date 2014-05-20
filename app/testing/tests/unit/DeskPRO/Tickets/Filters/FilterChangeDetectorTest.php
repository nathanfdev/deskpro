<?php
namespace DpUnitTests\DeskPRO\Tickets\Filters;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFilter;
use Application\DeskPRO\Monolog\Logger;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Filters\FilterChangeDetector;
use DpTestingMocks\ContainerMock;
use Mockery as m;

class FilterChangeDetectorTest extends \DpUnitTestCase
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;

	/**
	 * @var \Application\DeskPRO\Entity\Person[]
	 */
	private $agents;

	/**
	 * @var \Application\DeskPRO\Entity\TicketFilter[]
	 */
	private $filters;

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

	public function getAgentsArray()
	{
		if ($this->agents) return $this->agents;
		$this->agents = $this->createAgentObjects();
		return $this->agents;
	}

	public function getFilters()
	{
		if ($this->filters) return $this->filters;
		$this->filters = $this->createFilterObjects();
		return $this->filters;
	}

	public function createChangeDetector()
	{
		$logger = new Logger('changedetect');
		$logger->enableSavedMessages();

		$change_detector = new FilterChangeDetector(
			$this->getFilters(),
			$this->getAgentsArray()
		);

		return $change_detector;
	}

	####################################################################################################################

	public function testAssigned()
	{
		$ticket = new Ticket();
		$exec   = new ExecutorContext();

		$agents = $this->getAgentsArray();
		$set_agent = $agents[2];
		$ticket->agent = $set_agent;

		$change_detect = $this->createChangeDetector();
		$changeset = $change_detect->getFilterChangeSet($ticket, $exec);

		$this->assertCount(2, $changeset->getAffectedFilters());
	}

	public function testUnassigned()
	{
		$ticket = new Ticket();
		$exec   = new ExecutorContext();

		$agents = $this->getAgentsArray();
		$set_agent = $agents[2];
		$ticket->agent = $set_agent;

		$ticket->resetStateChangeRecorder();

		$ticket->agent = null;

		$change_detect = $this->createChangeDetector();
		$changeset = $change_detect->getFilterChangeSet($ticket, $exec);

		$this->assertCount(2, $changeset->getAffectedFilters());
	}

	####################################################################################################################

	/**
	 * @return \Application\DeskPRO\Entity\Person[]
	 */
	private function createAgentObjects()
	{
		$agents = array();

		$agent_helper = m::mock();
		$agent_helper->shouldReceive('getTeams')->andReturn(array(
			$this->getMockContainer()->getAgentData()->getTeam(5)
		));
		for ($i = 1; $i < 5; $i++) {
			$agent = m::mock('Application\\DeskPRO\\Entity\\Person')->makePartial();
			$agent->id = $i;
			$agent->is_agent = true;
			$agent->shouldReceive('getHelper')->andReturn($agent_helper);

			$agents[$i] = $agent;
		}

		return $agents;
	}


	/**
	 * @return \Application\DeskPRO\Entity\TicketFilter[]
	 */
	private function createFilterObjects()
	{
		$filter_data = <<<'JSON'
	[
		{
			"id": 1,
			"person_id": null,
			"agent_team_id": null,
			"is_global": 1,
			"title": "My Tickets",
			"is_enabled": 1,
			"sys_name": "agent",
			"terms": "a:3:{i:0;a:3:{s:4:\"type\";s:5:\"agent\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:5:\"agent\";s:2:\"-1\";}}i:1;a:3:{s:4:\"type\";s:6:\"status\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:6:\"status\";s:14:\"awaiting_agent\";}}i:2;a:3:{s:4:\"type\";s:7:\"is_hold\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:7:\"is_hold\";s:1:\"0\";}}}",
			"group_by": "",
			"order_by": "ticket.urgency:desc",
			"display_order": 0
		},
		{
			"id": 2,
			"person_id": null,
			"agent_team_id": null,
			"is_global": 1,
			"title": "My Team's Tickets",
			"is_enabled": 1,
			"sys_name": "agent_team",
			"terms": "a:3:{i:0;a:3:{s:4:\"type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:10:\"agent_team\";s:2:\"-1\";}}i:1;a:3:{s:4:\"type\";s:6:\"status\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:6:\"status\";s:14:\"awaiting_agent\";}}i:2;a:3:{s:4:\"type\";s:7:\"is_hold\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:7:\"is_hold\";s:1:\"0\";}}}",
			"group_by": "",
			"order_by": "ticket.urgency:desc",
			"display_order": 0
		},
		{
			"id": 4,
			"person_id": null,
			"agent_team_id": null,
			"is_global": 1,
			"title": "Unassigned",
			"is_enabled": 1,
			"sys_name": "unassigned",
			"terms": "a:3:{i:0;a:3:{s:4:\"type\";s:5:\"agent\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:5:\"agent\";s:1:\"0\";}}i:1;a:3:{s:4:\"type\";s:6:\"status\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:6:\"status\";s:14:\"awaiting_agent\";}}i:2;a:3:{s:4:\"type\";s:7:\"is_hold\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:7:\"is_hold\";s:1:\"0\";}}}",
			"group_by": "",
			"order_by": "ticket.urgency:desc",
			"display_order": 0
		}
	]
JSON;

		$filters = array();

		$filter_data = json_decode($filter_data, true);
		foreach ($filter_data as $data) {
			$data['terms'] = unserialize($data['terms']);

			$filter = new TicketFilter();
			foreach ($data as $k => $v) {
				$filter->$k = $v;
			}

			$filters[$data['sys_name']] = $filter;
		}

		return $filters;
	}
}