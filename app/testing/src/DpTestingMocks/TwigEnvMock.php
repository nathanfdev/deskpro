<?php

namespace DpTestingMocks;

use Application\DeskPRO\Entity\Person;
use Mockery as m;

class TwigEnvMock
{
	/**
	 * @var \Mockery\MockInterface
	 */
	private $mock;

	private function __construct()
	{
		$this->mock = m::mock('Application\\DeskPRO\\Twig\\Environment');
	}

	public static function create()
	{
		return new self();
	}

	public function withRenderStringTemplateNoop()
	{
		$this->mock->shouldReceive('renderStringTemplate')->andReturnUsing(function($string) {
			return $string;
		});
		return $this;
	}

	/**
	 * @return \Application\DeskPRO\Twig\Environment
	 */
	public function get()
	{
		return $this->mock;
	}
}