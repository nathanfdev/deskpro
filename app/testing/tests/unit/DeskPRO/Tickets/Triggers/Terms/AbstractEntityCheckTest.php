<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermInterface;

abstract class AbstractEntityCheckTest extends \DpUnitTestCase
{
	/**
	 * @var Ticket
	 */
	private $ticket;

	/**
	 * @var ExecutorContext
	 */
	private $exec_context;

	/**
	 * @var object
	 */
	protected $object1;

	/**
	 * @var object
	 */
	protected $object2;

	public function runBefore()
	{
		$ent_class = $this->getEntityClass();
		$prop_name = $this->getTicketPropertyName();

		$this->object1 = new $ent_class();
		$this->object1->id = 1;

		$this->object2 = new $ent_class();
		$this->object2->id = 1;

		$this->exec_context = new ExecutorContext();

		$this->ticket = new Ticket();
		$this->ticket->$prop_name = $this->object1;
	}

	/**
	 * @param string $op
	 * @param array $options
	 * @return TriggerTermInterface
	 */
	protected function createChecker($op, array $options)
	{
		$check_class = $this->getCheckClass();
		$opt_key     = $this->getCheckClassOptionKey();

		if (isset($options['%OPT%'])) {
			$options[$opt_key] = $options['%OPT%'];
			unset($options['%OPT%']);
		}

		$check = new $check_class($op, $options);
		return $check;
	}

	/**
	 * The term checker class
	 * @return string
	 */
	abstract protected function getCheckClass();

	/**
	 * The option key to supply IDs in
	 * @return string
	 */
	abstract protected function getCheckClassOptionKey();

	/**
	 * The entity class we are checking
	 * @return string
	 */
	abstract public function getEntityClass();

	/**
	 * The property on the ticket that is being checked
	 * @return string
	 */
	abstract public function getTicketPropertyName();

	public function testIsMatchSingleOption()
	{
		$check = $this->createChecker('is', array('%OPT%' => array(1)));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}

	public function testIsMatchMultipleOptions()
	{
		$check = $this->createChecker('is', array('%OPT%' => array(1,2)));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}

	public function testIsMatchInvalidOptions()
	{
		$check = $this->createChecker('is', array('%OPT%' => array('invalid',1,2,100)));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}

	public function testContainsMatchSingleOption()
	{
		$check = $this->createChecker('contains', array('%OPT%' => array(1)));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}

	public function testContainsMatchMultipleOptions()
	{
		$check = $this->createChecker('contains', array('%OPT%' => array(1,2)));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}

	public function testContainsMatchInvalidOptions()
	{
		$check = $this->createChecker('contains', array('%OPT%' => array('invalid',1,2,100)));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}

	public function testNotEmptyOption()
	{
		$check = $this->createChecker('not', array('%OPT%' => array()));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}

	public function testNotInvalidOption()
	{
		$check = $this->createChecker('not', array('%OPT%' => array('invalid')));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}

	public function testNotOption()
	{
		$check = $this->createChecker('not', array('%OPT%' => array(2)));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}

	public function testNotMultipleOptions()
	{
		$check = $this->createChecker('not', array('%OPT%' => array(500, 600)));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}

	public function testNotContainsSingleOption()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => array(500)));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}

	public function testNotContainsMultipleOptions()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => array(500,600)));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}

	public function testNotContainsInvalidOptions()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => array('invalid', 'abc')));
		$this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
	}
}