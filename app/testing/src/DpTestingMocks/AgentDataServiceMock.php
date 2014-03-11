<?php

namespace DpTestingMocks;

use Application\DeskPRO\Entity\Person;
use Mockery as m;

class AgentDataServiceMock
{
	/**
	 * @var \Mockery\MockInterface
	 */
	private $mock;

	private function __construct()
	{
		$this->mock = m::mock('Application\\DeskPRO\\DependencyInjection\\SystemServices\\AgentDataService');
	}

	public static function create()
	{
		return new self();
	}

	public function withStandard()
	{
		$this->mock->shouldReceive('get')->andReturnUsing(function($id) {
			static $agents = array();
			if (isset($agents[$id])) {
				return $agents[$id];
			}
			if ($id > 100) {
				return null;
			}
			$agents[$id]= new Person();
			$agents[$id]->id = $id;
			return $agents[$id];
		});
		return $this;
	}

	/**
	 * @return \Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService
	 */
	public function get()
	{
		return $this->mock;
	}
}