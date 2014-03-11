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

	public function withNullEm()
	{
		$em = m::mock('Application\\DeskPRO\\ORM\\EntityManager');
		$em->shouldIgnoreMissing();
		$this->mock->shouldReceive('getEm')->andReturn($em);
		$this->mock->shouldReceive('getOrm')->andReturn($em);
		return $this;
	}

	public function withAgentData($obj = null)
	{
		if ($obj === null) {
			$obj = AgentDataServiceMock::create()->withStandard()->get();
		}

		$this->mock->shouldReceive('getAgentData')->andReturn($obj);
		return $this;
	}

	public function withTwig($obj = null)
	{
		if ($obj === null) {
			$obj = TwigEnvMock::create()->withRenderStringTemplateNoop()->get();
		}

		$this->mock->shouldReceive('getTwig')->andReturn($obj);
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