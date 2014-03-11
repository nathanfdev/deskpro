<?php

namespace DpTestingMocks;

use Mockery as m;

class ContainerMock
{
	/**
	 * @var \Mockery\MockInterface
	 */
	private $mock;

	private function __construct()
	{
		$this->mock = m::mock('Application\\DeskPRO\\DependencyInjection\\DeskproContainer');
	}

	public static function create()
	{
		return new self();
	}

	public function withAgentData()
	{
		$this->mock->shouldReceive('getAgentData')->andReturn(AgentDataServiceMock::create()->withStandard()->get());
		return $this;
	}

	/**
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	public function get()
	{
		return $this->mock;
	}
}