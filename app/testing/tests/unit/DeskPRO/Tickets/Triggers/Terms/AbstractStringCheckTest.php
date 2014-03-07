<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermInterface;

abstract class AbstractStringCheckTest extends \DpUnitTestCase
{
	/**
	 * @var Ticket
	 */
	private $ticket1;

	/**
	 * @var Ticket
	 */
	private $ticket2;

	/**
	 * @var ExecutorContext
	 */
	private $exec_context;

	public function runBefore()
	{
		$prop_name = $this->getTicketPropertyName();

		$this->ticket1 = new Ticket();
		$this->ticket1->id = 1;
		$this->ticket1->$prop_name = "Test subject 123";
		$this->configureTicket($this->ticket1);

		$this->ticket2 = new Ticket();
		$this->ticket2->id = 2;
		$this->ticket2->$prop_name = "Test subject 123 ABC 100 200 XYZ";
		$this->configureTicket($this->ticket2);

		$this->exec_context = new ExecutorContext();
	}


	/**
	 * Override this in sub-classes to set additional properties on the test.
	 *
	 * @param Ticket $ticket
	 * @return void
	 */
	protected function configureTicket(Ticket $ticket)
	{
		// nothing
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
	 * The property on the ticket that is being checked
	 * @return string
	 */
	abstract public function getTicketPropertyName();


	################# op = is #################

	public function testIsEmptySearchMatch()
	{
		$check = $this->createChecker('is', array('%OPT%' => ''));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsExactMatch()
	{
		$check = $this->createChecker('is', array('%OPT%' => 'Test subject 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsExactCaseMatch()
	{
		$check = $this->createChecker('is', array('%OPT%' => 'TEST SUBJECT 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsWithPartialMatch()
	{
		$check = $this->createChecker('is', array('%OPT%' => 'Test subject 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testIsWithNoMatch()
	{
		$check = $this->createChecker('is', array('%OPT%' => 'Not a match'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}


	################# op = not #################

	public function testNotEmptySearchMatch()
	{
		$check = $this->createChecker('not', array('%OPT%' => ''));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testNotExactMatch()
	{
		$check = $this->createChecker('not', array('%OPT%' => 'Test subject 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testNotExactCaseMatch()
	{
		$check = $this->createChecker('not', array('%OPT%' => 'TEST SUBJECT 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testNotWithPartialMatch()
	{
		$check = $this->createChecker('not', array('%OPT%' => 'Test subject 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testNotWithNoMatch()
	{
		$check = $this->createChecker('not', array('%OPT%' => 'Not a match'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}


	################# op = contains #################

	public function testContainsEmptySearchMatch()
	{
		$check = $this->createChecker('contains', array('%OPT%' => ''));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testContainsExactMatch()
	{
		$check = $this->createChecker('contains', array('%OPT%' => 'Test subject 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testContainsExactCaseMatch()
	{
		$check = $this->createChecker('contains', array('%OPT%' => 'TEST SUBJECT 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testContainsPartialExactMatch()
	{
		$check = $this->createChecker('contains', array('%OPT%' => 'Test subject 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testContainsPartialCaseMatch()
	{
		$check = $this->createChecker('contains', array('%OPT%' => 'TEST SUBJECT 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testContainsNoMatch()
	{
		$check = $this->createChecker('contains', array('%OPT%' => 'Not a match'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}


	################# op = notcontains #################

	public function testNotContainsEmptySearchMatch()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => ''));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testNotContainsExactMatch()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => 'Test subject 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testNotContainsExactCaseMatch()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => 'TEST SUBJECT 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testNotContainsPartialExactMatch()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => 'Test subject 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testNotContainsPartialCaseMatch()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => 'TEST SUBJECT 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testNotContainsNoMatch()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => 'Not a match'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}


	################# op = is_regex #################

	public function testIsRegexNoDelims()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '[a-z]+'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsRegexWithDelims()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '/[a-z]+/'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsRegexWithDelims2()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '#[a-z]+#'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsRegexWithInvalidPattern()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '/invlaid.+++/'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsRegexWithAnchorStart()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '^Test'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsRegexWithAnchorEnd()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => 'XYZ$'));
		$this->assertTrue($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testIsRegexWithMod()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '/^test ticket/i'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsRegexWithEvalMod()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '/^test ticket/e'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}
}