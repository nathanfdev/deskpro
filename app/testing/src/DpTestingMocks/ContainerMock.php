<?php

namespace DpTestingMocks;

use Application\DeskPRO\Departments\TicketDepartments;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketWorkflow;
use Application\DeskPRO\Products\Products;
use Application\DeskPRO\Tickets\TicketCategories;
use Application\DeskPRO\Tickets\TicketPriorities;
use Application\DeskPRO\Tickets\TicketWorkflows;
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

	public function withTicketCategories($obj = null)
	{
		if ($obj === null) {
			$cats = array();
			for ($i = 1; $i < 100; $i++) {
				$c = new TicketCategory();
				$c->id = $i;
				$c->title = "Category $i";
				$cats[] = $c;
			}

			$repos = m::mock();
			$repos->shouldReceive('getCategories')->andReturn($cats);

			$em = m::mock('Application\\DeskPRO\\ORM\\EntityManager');
			$em->shouldIgnoreMissing();
			$em->shouldReceive('getRepository')->andReturn($repos);

			$obj = new TicketCategories($em);
		}

		$this->mock->shouldReceive('getTicketCategories')->andReturn($obj);
		return $this;
	}

	public function withTicketDepartments($obj = null)
	{
		if ($obj === null) {
			$cats = array();
			for ($i = 1; $i < 100; $i++) {
				$c = new Department();
				$c->id = $i;
				$c->title = "Department $i";
				$c->is_tickets_enabled = true;
				$cats[] = $c;
			}

			$repos = m::mock();
			$repos->shouldReceive('getTicketDepartments')->andReturn($cats);

			$em = m::mock('Application\\DeskPRO\\ORM\\EntityManager');
			$em->shouldIgnoreMissing();
			$em->shouldReceive('getRepository')->andReturn($repos);

			$obj = new TicketDepartments($em);
		}

		$this->mock->shouldReceive('getTicketDepartments')->andReturn($obj);
		return $this;
	}

	public function withTicketPriorities($obj = null)
	{
		if ($obj === null) {
			$cats = array();
			for ($i = 1; $i < 100; $i++) {
				$c = new TicketPriority();
				$c->id = $i;
				$c->title = "Priority $i";
				$cats[] = $c;
			}

			$repos = m::mock();
			$repos->shouldReceive('findAll')->andReturn($cats);
			$repos->shouldReceive('getAll')->andReturn($cats);

			$em = m::mock('Application\\DeskPRO\\ORM\\EntityManager');
			$em->shouldIgnoreMissing();
			$em->shouldReceive('getRepository')->andReturn($repos);

			$obj = new TicketPriorities($em);
		}

		$this->mock->shouldReceive('getTicketPriorities')->andReturn($obj);
		return $this;
	}

	public function withTicketWorkflows($obj = null)
	{
		if ($obj === null) {
			$cats = array();
			for ($i = 1; $i < 100; $i++) {
				$c = new TicketWorkflow();
				$c->id = $i;
				$c->title = "Workflow $i";
				$cats[] = $c;
			}

			$repos = m::mock();
			$repos->shouldReceive('findAll')->andReturn($cats);

			$em = m::mock('Application\\DeskPRO\\ORM\\EntityManager');
			$em->shouldIgnoreMissing();
			$em->shouldReceive('getRepository')->andReturn($repos);

			$obj = new TicketWorkflows($em);
		}

		$this->mock->shouldReceive('getTicketWorkflows')->andReturn($obj);
		return $this;
	}

	public function withProducts($obj = null)
	{
		if ($obj === null) {
			$cats = array();
			for ($i = 1; $i < 100; $i++) {
				$c = new Product();
				$c->id = $i;
				$c->title = "Product $i";
				$cats[] = $c;
			}

			$repos = m::mock();
			$repos->shouldReceive('findAll')->andReturn($cats);

			$em = m::mock('Application\\DeskPRO\\ORM\\EntityManager');
			$em->shouldIgnoreMissing();
			$em->shouldReceive('getRepository')->andReturn($repos);

			$obj = new Products($em);
		}

		$this->mock->shouldReceive('getProducts')->andReturn($obj);
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

    public function withElasticaRepositoryManager($obj = null)
    {
        if ($obj === null) {

            $obj = m::mock('FOS\\ElasticaBundle\\Doctrine\\RepositoryManager');

            $commonRepository = m::mock('FOS\\ElasticaBundle\\Repository');
            $commonRepository->shouldReceive('find')->withAnyArgs()->andReturn(array());

            $obj->shouldReceive('getRepository')->with('DeskPRO:Article')->andReturn($commonRepository);
            $obj->shouldReceive('getRepository')->with('DeskPRO:Download')->andReturn($commonRepository);
            $obj->shouldReceive('getRepository')->with('DeskPRO:Feedback')->andReturn($commonRepository);
            $obj->shouldReceive('getRepository')->with('DeskPRO:News')->andReturn($commonRepository);
            $obj->shouldReceive('getRepository')->with('DeskPRO:Person')->andReturn($commonRepository);
            $obj->shouldReceive('getRepository')->with('DeskPRO:Organization')->andReturn($commonRepository);
	        $obj->shouldReceive('getRepository')->with('DeskPRO:ChatConversation')->andReturn($commonRepository);

            $ticketRepository = m::mock('Application\\DeskPRO\\NewSearch\\Repository\\TicketRepository');
            $ticketRepository->shouldReceive('find')->withAnyArgs()->andReturn(array());
            $ticketRepository->shouldReceive('setPersonContext')->withAnyArgs();

            $obj->shouldReceive('getRepository')->with('DeskPRO:Ticket')->andReturn($ticketRepository);
        }

        $this->mock->shouldReceive('get')->with('fos_elastica.manager')->andReturn($obj);
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