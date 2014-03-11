<?php
namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Tickets\Actions\SetProduct;
use DpTestingMocks\ContainerMock;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

class SetProductTest extends \DpUnitTestCase
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
		$this->container = ContainerMock::create()->withProducts()->get();
		return $this->container;
	}

	public function testSet()
	{
		$ticket = new Ticket();
		$ticket->product = $this->getMockContainer()->getProducts()->getById(1);
		$exec   = new ExecutorContext();

		$action = new SetProduct(array('product_id' => 55));
		$action->setContainer($this->getMockContainer());

		$action->applyAction($ticket, $exec);

		$this->assertInstanceOf('Application\\DeskPRO\\Entity\\Product', $ticket->product);
		$this->assertEquals(55, $ticket->product->id);
	}

	public function testSetNull()
	{
		$ticket = new Ticket();
		$ticket->product = $this->getMockContainer()->getProducts()->getById(1);
		$exec   = new ExecutorContext();

		$action = new SetProduct(array('product_id' => 0));
		$action->setContainer($this->getMockContainer());

		$action->applyAction($ticket, $exec);

		$this->assertNull($ticket->product);
	}

	public function testNoop()
	{
		$ticket = new Ticket();
		$ticket->product = $this->getMockContainer()->getProducts()->getById(55);

		$exec = new ExecutorContext();

		$action = new SetProduct(array('product_id' => 55));
		$action->setContainer($this->container);

		$this->assertTrue($action->isNoop($ticket, $exec));
	}

	public function testInvalid()
	{
		$ticket = new Ticket();
		$exec = new ExecutorContext();

		$action = new SetProduct(array('product_id' => 200));
		$action->setContainer($this->getMockContainer());
		$action->applyAction($ticket, $exec);

		$this->assertNull($ticket->product);
	}
}